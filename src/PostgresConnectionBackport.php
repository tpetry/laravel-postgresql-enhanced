<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;

/**
 * To support some features these commits from laravel needed to be backported for older versions:
 * - [8.x] Adds new RefreshDatabaseLazily testing trait (https://github.com/laravel/framework/commit/3d1ead403e05ee0b9aa93a6ff720704970aec9c8)
 */
trait PostgresConnectionBackport
{
    /**
     * All of the callbacks that should be invoked before a query is executed.
     *
     * @var array
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
     * Run a SQL statement and log its execution context.
     *
     * @param string $query
     * @param array  $bindings
     */
    protected function run($query, $bindings, Closure $callback): mixed
    {
        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
            $beforeExecutingCallback($query, $bindings, $this);
        }

        return parent::run($query, $bindings, $callback);
    }
}
