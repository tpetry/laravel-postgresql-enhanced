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
use Tpetry\PostgresqlEnhanced\Query\Grammar as QueryGrammar;
use Tpetry\PostgresqlEnhanced\Schema\Builder as SchemaBuilder;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar as SchemaGrammar;
use Tpetry\PostgresqlEnhanced\Support\Helpers\ZeroDowntimeMigrationSupervisor;

class PostgresEnhancedConnection extends PostgresConnection
{
    use PostgresConnectionBackport;

    /**
     * Additional bindings which will be used in run().
     *
     * @var ?array{append: array<int, mixed>, prepend: array<int, mixed>}
     */
    private ?array $additionalBindings = null;

    /**
     * Get the query grammar used by the connection.
     */
    public function getQueryGrammar(): QueryGrammar
    {
        /** @var QueryGrammar $grammar */
        $grammar = parent::getQueryGrammar();

        return $grammar;
    }

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
     * Get the schema grammar used by the connection.
     */
    public function getSchemaGrammar(): ?SchemaGrammar
    {
        /** @var SchemaGrammar|null $grammar */
        $grammar = parent::getSchemaGrammar();

        return $grammar;
    }

    /**
     * Get a new query builder instance.
     */
    public function query(): QueryBuilder
    {
        return new QueryBuilder($this, $this->getQueryGrammar(), $this->getPostProcessor());
    }

    /**
     * Execute an SQL statement and return the results.
     */
    public function returningStatement(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }

            $statement = $this->prepared($this->getPdo()->prepare($query));

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            return $statement->fetchAll();
        });
    }

    /**
     * Run a query with additional bindings (used for CTEs).
     */
    public function runWithAdditionalBindings(callable $callback, array $prepend = [], array $append = []): mixed
    {
        try {
            $this->additionalBindings = compact('prepend', 'append');

            return $callback();
        } finally {
            $this->additionalBindings = null;
        }
    }

    /**
     * Return the version of the PostgreSQL database server.
     */
    public function serverVersion(): string
    {
        return $this->getPdo()->query('SHOW server_version')->fetchColumn();
    }

    /**
     * Get the default query grammar instance.
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        $grammar = new QueryGrammar();
        if (method_exists($grammar, 'setConnection')) {
            $grammar->setConnection($this);
        }

        return $grammar;
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        $grammar = new SchemaGrammar();
        $grammar->setTablePrefix($this->tablePrefix);
        if (method_exists($grammar, 'setConnection')) {
            $grammar->setConnection($this);
        }

        return $grammar;
    }

    /**
     * Handle a query exception.
     *
     * @param string $query
     * @param array $bindings
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
     * @param int $currentAttempt
     * @param int $maxAttempts
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

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param string $query
     * @param array<int, mixed> $bindings
     */
    protected function run($query, $bindings, Closure $callback): mixed
    {
        $bindings = [
            ...$this->additionalBindings['prepend'] ?? [],
            ...$bindings,
            ...$this->additionalBindings['append'] ?? [],
        ];

        return parent::run($query, $bindings, $callback);
    }
}
