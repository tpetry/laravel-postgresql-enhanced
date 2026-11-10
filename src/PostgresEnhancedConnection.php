<?php

declare(strict_types=1);

// @php-cs-fixer-ignore void_return -- bindValues() must stay without return type, see its comment.

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use PDO;
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
     * Bind values to their parameters in the given statement.
     * No native return type: Laravel declares none, so user subclasses written against it must stay compatible.
     *
     * @param \PDOStatement $statement
     * @param array $bindings
     *
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        $usesNativeBooleans = PostgresqlEnhanced::usesNativeBooleans();
        $detectsBinaryStrings = PostgresqlEnhanced::detectsBinaryStrings();
        foreach ($bindings as $key => $value) {
            // Booleans only survive prepareBindings() when native booleans are enabled. They are bound with
            // PDO::PARAM_BOOL, which pdo_pgsql sends as 't'/'f'. Strings containing NUL bytes or invalid UTF-8 are bound
            // as LOBs (bytea) when binary string detection is enabled, as a text parameter can't hold them.
            $type = match (true) {
                $usesNativeBooleans && \is_bool($value) => PDO::PARAM_BOOL,
                $detectsBinaryStrings && (\is_resource($value) || $this->isBinaryString($value)) => PDO::PARAM_LOB,
                default => null,
            };
            if (null === $type) {
                continue;
            }

            $statement->bindValue(\is_string($key) ? $key : $key + 1, $value, $type);
            unset($bindings[$key]);
        }

        parent::bindValues($statement, $bindings);
    }

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
     * Prepare the query bindings for execution.
     * No native return type: Laravel declares none, so user subclasses written against it must stay compatible.
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $prepared = parent::prepareBindings($bindings);

        if (!PostgresqlEnhanced::usesNativeBooleans()) {
            return $prepared;
        }

        $emulatesPrepares = $this->usesEmulatedPreparesCompat();
        foreach ($bindings as $key => $value) {
            if (\is_bool($value)) {
                // With real prepared statements the PHP bool is kept for bindValues(), which binds it with PDO::PARAM_BOOL
                // so pdo_pgsql sends it as 't'/'f'. With emulated
                // prepares the value is interpolated client side, so the literal strings Laravel 13.17 introduced for
                // that case are used instead (laravel/framework#60425).
                $prepared[$key] = $emulatesPrepares ? ($value ? 'true' : 'false') : $value;
            }
        }

        return $prepared;
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

        if (PostgresqlEnhanced::detectsBinaryStrings() && array_filter($bindings, fn (mixed $value): bool => \is_resource($value) || $this->isBinaryString($value))) {
            // LOB bindings (binary strings and streams) are rejected by the statement initializing a lazy transaction, as
            // its emulated prepares can't transport binary data. So a queued lazy transaction is started first and the
            // query is a normal prepared statement. This happens inside the callback so a lost connection is handled by handleQueryException().
            $callback = function (...$arguments) use ($callback): mixed {
                // A lazy transaction can only exist inside a transaction, so the PDO is not resolved otherwise. This
                // avoids opening the write connection for a read outside of transactions.
                $pdo = !$this->pretending() && 0 < $this->transactionLevel() ? $this->getPdo() : null;
                if ($pdo instanceof PostgresqlEnhancedPdoConnection) {
                    $pdo->executeQueuedQueriesAndStopQueuing();
                }

                return $callback(...$arguments);
            };
        }

        return parent::run($query, $bindings, $callback);
    }

    /**
     * Whether the value is a string only a bytea parameter can hold: it contains NUL bytes or is not valid UTF-8.
     *
     * Binary data that happens to be valid UTF-8 without NUL bytes can't be told apart from text. It is still sent as
     * text, so PostgreSQL parses it as bytea escape input (`\x41` becomes `A`, a lone backslash raises an error).
     */
    private function isBinaryString(mixed $value): bool
    {
        // preg_match() with the /u modifier validates UTF-8 without requiring the mbstring extension.
        return \is_string($value) && (str_contains($value, "\0") || 1 !== preg_match('//u', $value));
    }

    /**
     * Whether the PDO connection emulates prepared statements.
     */
    private function usesEmulatedPreparesCompat(): bool
    {
        // Laravel 13.17 added PostgresConnection::usesEmulatedPrepares() which also honours read/write connection
        // configs (https://github.com/laravel/framework/pull/60425). Older versions fall back to the plain config.
        // @phpstan-ignore function.alreadyNarrowedType (Laravel < 13.17 has no usesEmulatedPrepares())
        if (method_exists($this, 'usesEmulatedPrepares')) {
            return (bool) $this->usesEmulatedPrepares();
        }

        return (bool) ($this->getConfig('options')[PDO::ATTR_EMULATE_PREPARES] ?? false);
    }
}
