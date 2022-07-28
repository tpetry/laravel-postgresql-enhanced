<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

trait BuilderExtension
{
    /**
     * Create a new extension on the schema.
     */
    public function createExtension(string $name): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);
        $this->getConnection()->statement("create extension {$name}");
    }

    /**
     * Create a new extension on the schema if it does not exist.
     */
    public function createExtensionIfNotExists(string $name): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);
        $this->getConnection()->statement("create extension if not exists {$name}");
    }

    /**
     * Drop extensions from the schema.
     */
    public function dropExtension(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop extension {$names}");
    }

    /**
     * Drop extensions from the schema if they exist.
     */
    public function dropExtensionIfExists(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop extension if exists {$names}");
    }

    /**
     * Create function.
     */
    public function createFunction(string $name, string|array $parameters, string $return, string $body, array $modifiers = [], bool $replace = false): void
    {
        $parameters = is_string($parameters) ? $parameters : implode(', ', array_map(function (string $key, string $value) {
          return "$key $value";
        }, array_keys($parameters), array_values($parameters)));

        // Compile modifiers
        $preparedModifiers = [];
        $defaultModifiers = [
            'language' => 'plpgsql',
        ];
        $mergedModifiers = array_merge($defaultModifiers, $modifiers);
        array_walk($mergedModifiers, function ($value, $key) use(&$preparedModifiers) {
            $preparedModifier = match(strtolower($key)) {
                'language' => sprintf('LANGUAGE %1$s', $value),
                'transform' => sprintf('TRANSFORM %1$s', $value),
                'mutability' => $value,
                'leakproof' => $value ? 'LEAKPROOF' : 'NOT LEAKPROOF',
                'strict' => $value === true ? 'STRICT' : $value,
                'security' => $value,
                'parallel' => sprintf('PARALLEL %1$s', $value),
                'cost' => sprintf('COST %1$s', $value),
                'rows' => sprintf('ROWS %1$s', $value),
                'support' => sprintf('SUPPORT %1$s', $value),
                'set' => sprintf('SET %1$s', $value),
                'as' => sprintf('AS %1$s', $value),
            };

            if ($preparedModifier) {
                array_push($preparedModifiers, $preparedModifier);
            }
        });

        // Use atomic mode in PLPGSQL if possible
        if (strtolower($mergedModifiers['language']) === 'plpgsql') {
            $version = $this->getConnection()->selectOne('SHOW server_version')->server_version;
            if (version_compare($version, '14') >= 0) {
                $body = preg_replace('/BEGIN(?! atomic)/i', 'BEGIN ATOMIC', $body);
            }
        }

        // Create function
        $compiledModifiers = implode(' ', $preparedModifiers);
        $this->getConnection()->statement(sprintf(
            '%1$s FUNCTION %2$s(%3$s) RETURNS %4$s AS $$ %5$s $$ %6$s',
            $replace ? 'CREATE OR REPLACE' : 'CREATE',
            $name,
            $parameters,
            $return,
            $body,
            $compiledModifiers
        ));
    }

    /**
     * Create or replace function.
     */
    public function createOrReplaceFunction(string $name, string|array $parameters, string $return, string $body, array $modifiers = []): void
    {
        $this->createFunction($name, $parameters, $return, $body, $modifiers, true);
    }

    /**
     * Drop function.
     */
    public function dropFunction(string $name): void
    {
        $this->getConnection()->statement(sprintf(
            'DROP FUNCTION %1$s',
            $name
        ));
    }
}
