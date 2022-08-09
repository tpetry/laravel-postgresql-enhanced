<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Arr;

trait BuilderFunction
{
    /**
     * Create a new function on the schema.
     *
     * @param array<string, string> $parameters
     * @param array{calledOnNull?: bool, cost?: int, leakproof?: bool, parallel?: 'restricted'|'safe'|'unsafe', security?: 'definer'|'invoker', volatility?: 'immutable'|'stable'|'volatile'} $options
     */
    public function createFunction(string $name, array $parameters, string $return, string $language, string $body, array $options = []): void
    {
        $this->createGenericFunction($name, $parameters, $return, $language, $body, $options, false);
    }

    /**
     * Create or replace a new function on the schema.
     *
     * @param array<string, string> $parameters
     * @param array{calledonnull?: bool, cost?: int, leakproof?: bool, parallel?: 'restricted'|'safe'|'unsafe', security?: 'definer'|'invoker', volatility?: 'immutable'|'stable'|'volatile'} $options
     */
    public function createFunctionOrReplace(string $name, array $parameters, string $return, string $language, string $body, array $options = []): void
    {
        $this->createGenericFunction($name, $parameters, $return, $language, $body, $options, true);
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

    /**
     * Create a new function on the schema.
     *
     * @param array<string, string> $parameters
     * @param array{calledonnull?: bool, cost?: int, leakproof?: bool, parallel?: 'restricted'|'safe'|'unsafe', security?: 'definer'|'invoker', volatility?: 'immutable'|'stable'|'volatile'} $options
     */
    private function createGenericFunction(string $name, array $parameters, string $return, string $language, string $body, array $options, bool $createOrReplace): void
    {
        [$language, $languageOption] = match ($language) {
            'sql:expression' => ['sql', 'expression'],
            default => [$language, null],
        };

        $parameters = array_map(function (string $name, string $type) {
            return "{$this->getConnection()->getSchemaGrammar()->wrap($name)} {$type}";
        }, array_keys($parameters), array_values($parameters));
        $parametersStr = implode(', ', $parameters);

        $modifiers = ["language {$language}"];
        foreach ($options as $key => $value) {
            $modifiers[] = match (true) {
                'calledOnNull' === $key => $value ? 'called on null input' : 'returns null on null input',
                'leakproof' === $key => $value ? 'leakproof' : 'not leakproof',
                'volatility' === $key => $value,
                \in_array($key, ['cost', 'parallel', 'security']) => "{$key} {$value}",
                default => throw_if(true, message: "Unknown option '{$key}'."),
            };
        }
        $sqlModifiers = implode(' ', $modifiers);

        $supportsSqlFunctionBodies = version_compare($this->getConnection()->serverVersion(), '14') >= 0;
        $sqlBody = match (true) {
            'sql' === $language && 'expression' === $languageOption && $supportsSqlFunctionBodies => "return ({$body})",
            'sql' === $language && 'expression' === $languageOption => "as $$ select ({$body}) $$",
            'sql' === $language && $supportsSqlFunctionBodies => "begin atomic; {$body}; end",
            default => "as $$ {$body} $$",
        };

        $sqlCreate = match ($createOrReplace) {
            true => "create or replace function {$this->getConnection()->getSchemaGrammar()->wrap($name)}({$parametersStr}) returns {$return}",
            false => "create function {$this->getConnection()->getSchemaGrammar()->wrap($name)}({$parametersStr}) returns {$return}",
        };

        $this->getConnection()->statement("{$sqlCreate} {$sqlModifiers} {$sqlBody}");
    }
}
