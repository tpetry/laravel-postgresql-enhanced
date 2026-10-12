<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use PDO;
use Pdo\Pgsql;
use PDOException;
use PDOStatement;
use SensitiveParameter;
use Tpetry\PostgresqlEnhanced\Exceptions\LazyTransactionLostConnectionException;
use WeakReference;

/**
 * The whole idea of the enhanced PDO connection is to support the lazy transaction mode. If you don't use a lazy
 * transaction, the pdo connection behaves like a normal PDO connection. The behavior with lazy transaction is this:
 * 1. `$lazyTransaction = false`: Everything works as normal.
 * 2. `$lazyTransaction = true`: The next begin() will start a lazy transaction.
 * 3. `$lazyTransaction = array`: Queries that can be safely queued and merged into one single query are pushed into it.
 *       Many queries can be added until one query is executed that is non-queueable. Then the queued queries are
 *       executed together with the new query. With prepare() it is a little bit more complicated: PDO creates a
 *       statement that can be executed later anytime. So to ensure the transactional semantics, the behavior changes
 *       when the first prepare() is called: `$lazyTransaction` is set to a weak reference of the prepared statement.
 * 4. `$lazyTransaction = WeakReference`: Indicates that this query will fully initialize a transaction with the queued
 *       queries. So technically the transaction has not started. But the code now enforces that the next PDO operation
 *       MUST be the execution of that statement. No other commands are allowed to run because otherwise would expect a
 *       running transaction, but there is none. That statement is also only allowed to run once because otherwise the
 *       behavior is unpredictable for any DML query. However, you can also roll back the transaction when the property
 *       is set, which just clears the statement as the behavior is like when the query never executed.
 *
 * Note: The statement is stored as a weak reference for garbage collection purposes: When user code throws it away and
 *       the pdo connection also, there is still a reference cycle from connection to statement and vice versa. The
 *       garbage collector can clean this up, but it is running the check for cycles much less often than normal gc
 *       operations. So the weak reference helps with closing the connection much faster.
 */
class PostgresqlEnhancedPdoConnection extends Pgsql
{
    /**
     * The current lazy transaction mode:
     * - `false`: The next begin() should not start a lazy transaction.
     * - `true`: The next begin() should start a lazy transaction.
     * - `array`: A lazy transaction is in progress. The array contains the queued queries.
     * - `WeakReference`: A lazy transaction is in progress. The statement contains the final query to execute.
     *
     * @internal
     *
     * @var array<int, string>|bool|WeakReference<PostgresqlEnhancedPdoStatement>
     */
    public array|bool|WeakReference $lazyTransaction = false;

    private static array $listeners = [];

    public function __construct(string $dsn, ?string $username = null, #[SensitiveParameter] ?string $password = null, ?array $options = null)
    {
        parent::__construct($dsn, $username, $password, $options);

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PostgresqlEnhancedPdoStatement::class, [WeakReference::create($this)]]);
    }

    #[\Override]
    public function beginTransaction(): bool
    {
        if (\is_array($this->lazyTransaction) || $this->lazyTransaction instanceof WeakReference) {
            throw new \RuntimeException('A lazy transaction is already in progress. It can\'t be started twice.');
        }
        if (true === $this->lazyTransaction) {
            $this->lazyTransaction = ['begin'];

            return true;
        }

        $this->broadcastExecuteQuery('begin');

        return parent::beginTransaction();
    }

    /**
     * @internal
     */
    public function broadcastExecuteQuery(string $query): void
    {
        foreach (self::$listeners as $listener) {
            $listener($query, $this);
        }
    }

    #[\Override]
    public function commit(): bool
    {
        if ($this->lazyTransaction instanceof WeakReference) {
            // Unlike the rollback() implementation, the commit() can't drop a non-executed initializer because it may
            // contain a DML query which then would have never been executed.
            throw new \RuntimeException('The prepared statement with the lazy transaction initialization queries has not executed yet.');
        }
        if (\is_array($this->lazyTransaction)) {
            $this->lazyTransaction = false;

            return true;
        }

        $this->broadcastExecuteQuery('commit');

        return parent::commit();
    }

    #[\Override]
    public function exec(string $query): int|false
    {
        if ($this->lazyTransaction instanceof WeakReference) {
            throw new \RuntimeException('The prepared statement with the lazy transaction initialization queries has not executed yet.');
        }
        if (\is_array($this->lazyTransaction)) {
            if ($this->isQueueableByLazyTransaction($query)) {
                $this->lazyTransaction[] = $query;

                return 0;
            }

            return $this->executeQueuedQueriesAndStopQueuing($query);
        }

        $this->broadcastExecuteQuery($query);

        return parent::exec($query);
    }

    /**
     * @internal
     */
    public function executeQueuedQueriesAndStopQueuing(?string $mergeAdditionalQuery = null): int|false
    {
        if (!\is_array($this->lazyTransaction)) {
            return 0;
        }

        $lazyTransaction = $this->lazyTransaction;
        $queries = null !== $mergeAdditionalQuery ? [...$this->lazyTransaction, $mergeAdditionalQuery] : $this->lazyTransaction;
        $this->lazyTransaction = false;
        $this->broadcastExecuteQuery($query = implode("\n;", $queries));

        try {
            return parent::exec($query);
        } catch (PDOException $e) {
            if ('42601' === $e->getCode()) {
                // A syntax error rejected the whole merged query, not even `BEGIN` was executed. Executing the queries
                // one by one throws the exception for the failing query and leaves the transaction in the state it
                // would have been without merged queries.
                foreach ($queries as $query) {
                    $this->exec($query);
                }
            }

            throw LazyTransactionLostConnectionException::wrapIfLostConnection($e, $lazyTransaction);
        }
    }

    public static function flushListeners(): void
    {
        self::$listeners = [];
    }

    #[\Override]
    public function inTransaction(): bool
    {
        if (\is_array($this->lazyTransaction) || $this->lazyTransaction instanceof WeakReference) {
            return true;
        }

        return parent::inTransaction();
    }

    /**
     * @param Closure(string $query, self $pdo): void $callback
     */
    public static function listen(Closure $callback): void
    {
        self::$listeners[] = $callback;
    }

    #[\Override]
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        if ($this->lazyTransaction instanceof WeakReference) {
            throw new \RuntimeException('The prepared statement with the lazy transaction initialization queries has not executed yet.');
        }

        if (\is_array($this->lazyTransaction)) {
            // A real prepared statement can't contain multiple statements. But an emulated one is sent as a simple
            // query which can, so the queued queries are executed together with the prepared statement in emulated mode
            // and stops the queuing behavior of the lazy transaction.
            $statement = parent::prepare(implode("\n;", [...$this->lazyTransaction, $query]), [
                PDO::ATTR_EMULATE_PREPARES => true,
                PDO::ATTR_PREFETCH => true,
                PDO::ATTR_STATEMENT_CLASS => [PostgresqlEnhancedPdoStatement::class, [WeakReference::create($this), true, [...$this->lazyTransaction, $query]]],
            ] + $options);
            if (false !== $statement) {
                $this->lazyTransaction = WeakReference::create($statement);
            }

            return $statement;
        }

        return parent::prepare($query, $options);
    }

    #[\Override]
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        if ($this->lazyTransaction instanceof WeakReference) {
            throw new \RuntimeException('The prepared statement with the lazy transaction initialization queries has not executed yet.');
        }

        // The requested query could be merged with queued queries, but Laravel doesn't use this way of executing
        // queries anywhere. To simplify the implementation, the queued statements are executed first and then the
        // requested query. There's no need to complicate the implementation for something probably not used. The only
        // goal here is to make it valid to use the method, but not an optimized implementation.
        $this->executeQueuedQueriesAndStopQueuing();
        $this->broadcastExecuteQuery($query);

        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }

    #[\Override]
    public function rollBack(): bool
    {
        if (\is_array($this->lazyTransaction) || $this->lazyTransaction instanceof WeakReference) {
            $this->lazyTransaction = false;

            return true;
        }

        $this->broadcastExecuteQuery('rollback');

        return parent::rollBack();
    }

    public function useLazyTransactionForNextBegin(): void
    {
        if (\is_array($this->lazyTransaction) || $this->lazyTransaction instanceof WeakReference) {
            throw new \RuntimeException('A lazy transaction is already in progress.');
        }

        $this->lazyTransaction = true;
    }

    private function isQueueableByLazyTransaction(string $query): bool
    {
        // Multi-Statement queries sent by the user should not be queued because it is impossible to say whether they
        // are safe to queue. Sure, they could be split and each one checked, but that is not easy as correct query
        // splitting has many edge cases. This is a good-enough check which will ignore semicolons at the end of a query
        // but fail when in the middle of a query (which is a rare edge case).
        if (str_contains(rtrim($query, "\t\n\r\; "), ';')) {
            return false;
        }

        // Allowed to queue:
        // - SET LOCAL {configuration}
        // - SAVEPOINT {name}
        // - RELEASE [SAVEPOINT] {name}
        // - ROLLBACK [WORK|TRANSACTION] TO [SAVEPOINT] {name}
        return 1 === preg_match('/^\s*(?:set\s+local|savepoint|release(?:\s+savepoint)?|rollback(?:\s+work|\s+transaction)?\s+to(?:\s+savepoint)?)\s+/i', $query);
    }
}
