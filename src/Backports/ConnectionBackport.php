<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Backports;

use Closure;
use RuntimeException;
use Tpetry\PostgresqlEnhanced\Query\Grammar as QueryGrammar;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar as SchemaGrammar;

/**
 * To support some features these commits from laravel needed to be backported for older versions:
 * - [8.x] Adds new RefreshDatabaseLazily testing trait (https://github.com/laravel/framework/commit/3d1ead403e05ee0b9aa93a6ff720704970aec9c8).
 * - [10.x] Escaping functionality within the Grammar (https://github.com/laravel/framework/commit/e953137280cdf6e0fe3c3e4c49d7209ad86c92c0).
 */
trait ConnectionBackport
{
    /**
     * All of the callbacks that should be invoked before a query is executed.
     *
     * @var Closure[]
     */
    protected $beforeExecutingCallbacks = [];

    /**
     * Register a hook to be run just before a database query is executed.
     */
    public function beforeExecuting(Closure $callback): static
    {
        $this->beforeExecutingCallbacks[] = $callback;

        return $this;
    }

    /**
     * Escape a value for safe SQL embedding.
     *
     * @param string|float|int|bool|null $value
     * @param bool $binary
     */
    public function escape($value, $binary = false): string
    {
        if (null === $value) {
            return 'null';
        } elseif ($binary) {
            return $this->escapeBinary($value);
        } elseif (\is_int($value) || \is_float($value)) {
            return (string) $value;
        } elseif (\is_bool($value)) {
            return $this->escapeBool($value);
        } else {
            if (str_contains($value, "\00")) {
                throw new RuntimeException('Strings with null bytes cannot be escaped. Use the binary escape option.');
            }
            if (false === preg_match('//u', $value)) {
                throw new RuntimeException('Strings with invalid UTF-8 byte sequences cannot be escaped.');
            }

            return $this->escapeString($value);
        }
    }

    /**
     * Escape a binary value for safe SQL embedding.
     *
     * @param string $value
     */
    protected function escapeBinary($value): string
    {
        $hex = bin2hex($value);

        return "'\x{$hex}'::bytea";
    }

    /**
     * Escape a boolean value for safe SQL embedding.
     *
     * @param bool $value
     */
    protected function escapeBool($value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * Escape a string value for safe SQL embedding.
     *
     * @param string $value
     */
    protected function escapeString($value): string
    {
        return $this->getPdo()->quote($value);
    }

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        ($grammar = new QueryGrammar())->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        ($grammar = new SchemaGrammar())->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param string $query
     * @param array $bindings
     */
    protected function run($query, $bindings, Closure $callback): mixed
    {
        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
            $beforeExecutingCallback($query, $bindings, $this);
        }

        return parent::run($query, $bindings, $callback);
    }
}
