<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Throwable;
use Tpetry\PostgresqlEnhanced\Query\Builder as QueryBuilder;
use Tpetry\PostgresqlEnhanced\Schema\Builder as SchemaBuilder;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;
use Tpetry\PostgresqlEnhanced\Support\Helpers\ZeroDowntimeMigrationSupervisor;

class PostgresEnhancedConnection extends PostgresConnection
{
    use PostgresConnectionBackport;

    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): SchemaBuilder
    {
        if (null === $this->schemaGrammar) {
            $this->useDefaultSchemaGrammar();
        }

        return new SchemaBuilder($this);
    }

    /**
     * Get a new query builder instance.
     */
    public function query(): QueryBuilder
    {
        return new QueryBuilder($this, $this->getQueryGrammar(), $this->getPostProcessor());
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): Grammar
    {
        return $this->withTablePrefix(new Grammar());
    }

    /**
     * Handle a query exception.
     *
     * @param string $query
     * @param array  $bindings
     */
    protected function handleQueryException(QueryException $e, $query, $bindings, Closure $callback): mixed
    {
        $zeroDowntimeMigrationErrors = [
            'canceling statement due to statement timeout',
            'terminating connection due to idle-in-transaction timeout',
        ];

        /** @var ZeroDowntimeMigrationSupervisor $supervisor */
        $supervisor = Container::getInstance()->get(ZeroDowntimeMigrationSupervisor::class);
        if ($supervisor->isZeroDowntimeMigrationRunning() && Str::contains($e->getMessage(), $zeroDowntimeMigrationErrors)) {
            $supervisor->stop();

            throw new ZeroDowntimeMigrationTimeoutException('Zero downtime migration timeout exceeded.', previous: $e);
        }

        return parent::handleQueryException($e, $query, $bindings, $callback);
    }

    /**
     * Handle an exception encountered when running a transacted statement.
     *
     * @param Throwable $e
     * @param int       $currentAttempt
     * @param int       $maxAttempts
     */
    protected function handleTransactionException($e, $currentAttempt, $maxAttempts): void
    {
        try {
            parent::handleTransactionException($e, $currentAttempt, $maxAttempts);
        } catch (Throwable $handleException) {
            if ($e instanceof ZeroDowntimeMigrationTimeoutException) {
                Container::getInstance()->get(ZeroDowntimeMigrationSupervisor::class)->stop();

                // The laravel handleTransactionException handler will try to rollback any changes done in the failed
                // transaction. In case on a ZeroDowntimeMigrationTimeoutException the database connection will
                // probably already be closed so trying to rollback would throw new exception. When the $handleException
                // is thrown all operations of the transaction have been reversed and the timeout exception can be
                // thrown again.
                throw $e;
            }

            throw $handleException;
        }
    }
}
