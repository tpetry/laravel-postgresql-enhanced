<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Tpetry\PostgresqlEnhanced\Backports\ConnectionBackport;
use Tpetry\PostgresqlEnhanced\Connection\Transaction;
use Tpetry\PostgresqlEnhanced\Exceptions\LazyTransactionLostConnectionException;
use Tpetry\PostgresqlEnhanced\Query\Builder as QueryBuilder;
use Tpetry\PostgresqlEnhanced\Query\Grammar as QueryGrammar;
use Tpetry\PostgresqlEnhanced\Schema\Builder as SchemaBuilder;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar as SchemaGrammar;
use Tpetry\PostgresqlEnhanced\Support\Helpers\ZeroDowntimeMigrationSupervisor;

class PostgresEnhancedConnection extends PostgresConnection
{
    use ConnectionBackport;
    use Transaction;

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
        $grammar = new QueryGrammar($this);
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        $grammar = new SchemaGrammar($this);
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }

    /**
     * Handle a query exception.
     *
     * @param string $query
     * @param array $bindings
     */
    #[\Override]
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

        // A lazy transaction is started on the database with the first unqueueable query, so a lost connection is only
        // detected when that query fails. Like Laravel does for a failing `BEGIN`, the connection is renewed and the
        // transaction started again. This is safe as nothing of the query is ever executed on the database.
        $previous = $e->getPrevious();
        if ($previous instanceof LazyTransactionLostConnectionException) {
            // Reconnecting resets the transaction level and rolls back the transactions manager, which drops the
            // transaction state and executes the rollback callbacks. That's not what we want as the reconnection should
            // happen invisibly. So the current state is kept and reapplied after reconnecting, so we can continue with
            // Laravel's usual exception handling to rerun the actual query which will start a lazy transaction again.
            with(['level' => $this->transactions, 'manager' => $this->transactionsManager], function (array $transactionState): void {
                $this->unsetTransactionManager();
                $this->reconnect();
                $this->setTransactionManager($transactionState['manager']);
                $this->transactions = $transactionState['level'];
            });

            /** @var PostgresqlEnhancedPdoConnection $pdo */
            $pdo = $this->getPdo();
            $pdo->lazyTransaction = $previous->getQueuedQueries();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        return parent::handleQueryException($e, $query, $bindings, $callback);
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
