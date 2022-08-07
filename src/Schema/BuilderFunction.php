<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Arr;

trait BuilderFunction
{
    /**
     * Create function.
     */
    public function createFunction(string $name, string|array $parameters, string $return, string $body, array $modifiers = [], bool $replace = false): void
    {
        $parameters = \is_string($parameters) ? $parameters : implode(', ', array_map(function (string $key, string $value) {
            return "$key $value";
        }, array_keys($parameters), array_values($parameters)));

        // Compile modifiers
        $preparedModifiers = [];
        $defaultModifiers = [
            'language' => 'plpgsql',
        ];
        $mergedModifiers = array_merge($defaultModifiers, $modifiers);
        array_walk($mergedModifiers, function ($value, $key) use (&$preparedModifiers): void {
            $preparedModifier = match (strtolower($key)) {
                'language' => sprintf('LANGUAGE %1$s', $value),
                'transform' => sprintf('TRANSFORM %1$s', $value),
                'mutability' => $value,
                'leakproof' => $value ? 'LEAKPROOF' : 'NOT LEAKPROOF',
                'strict' => true === $value ? 'STRICT' : $value,
                'security' => $value,
                'parallel' => sprintf('PARALLEL %1$s', $value),
                'cost' => sprintf('COST %1$s', $value),
                'rows' => sprintf('ROWS %1$s', $value),
                'support' => sprintf('SUPPORT %1$s', $value),
                'set' => sprintf('SET %1$s', $value),
                'as' => sprintf('AS %1$s', $value),
                default => throw_if(true, message: "Unknown modifier '{$key}'.")
            };

            if ($preparedModifier) {
                $preparedModifiers[] = $preparedModifier;
            }
        });

        // Use atomic mode in PLPGSQL if possible
        if ('plpgsql' === strtolower($mergedModifiers['language'])) {
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
     * Drop function from the schema.
     *
     * @param ?array<int, string> $arguments
     */
    public function dropFunction(string $name, ?array $arguments = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);
        $argumentsStr = implode(',', Arr::wrap($arguments));

        $sql = match (\is_array($arguments)) {
            true => "drop function {$name}({$argumentsStr})",
            false => "drop function {$name}",
        };
        $this->getConnection()->statement($sql);
    }

    /**
     * Drop function from the schema if they exist.
     *
     * @param ?array<int, string> $arguments
     */
    public function dropFunctionIfExists(string $name, ?array $arguments = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);
        $argumentsStr = implode(',', Arr::wrap($arguments));

        $sql = match (\is_array($arguments)) {
            true => "drop function if exists {$name}({$argumentsStr})",
            false => "drop function if exists {$name}",
        };
        $this->getConnection()->statement($sql);
    }
}
