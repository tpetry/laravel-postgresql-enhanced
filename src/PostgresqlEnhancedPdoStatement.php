<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use Tpetry\PostgresqlEnhanced\Exceptions\LazyTransactionLostConnectionException;
use WeakReference;

class PostgresqlEnhancedPdoStatement extends PDOStatement
{
    private bool $hasExecuted = false;

    /**
     * @param WeakReference<PostgresqlEnhancedPdoConnection> $connection
     * @param array<int, string> $lazyTransactionQueries the queued queries and the prepared query merged into this statement
     */
    protected function __construct(
        private WeakReference $connection,
        private bool $startsLazyTransaction = false,
        private array $lazyTransactionQueries = [],
    ) {
    }

    #[\Override]
    public function bindParam(int|string $param, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool
    {
        if ($this->startsLazyTransaction) {
            // Laravel doesn't use bindParam(), so the implementation can be simplified a lot just by not allowing this
            // method for the transaction initializer statement.
            throw new \LogicException('bindParam() is not supported for the prepared statement with the lazy transaction initialization queries, use bindValue() instead.');
        }

        return parent::bindParam($param, $var, $type, $maxLength, $driverOptions);
    }

    #[\Override]
    public function bindValue(int|string $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        // Emulated prepares (as used by the lazy transaction initializer) embed the values into the query which must
        // result in the same behavior as real prepares:
        // - Integers are embedded as integer literals but must be untyped quoted strings like with real prepares.
        // - Booleans and integers bound as boolean are sent as 't' or 'f' by real prepares, but would be embedded as
        //   integer literals.
        if ($this->startsLazyTransaction) {
            if (PDO::PARAM_LOB === $type) {
                // This is safe to reject as Laravel doesn't use them either way.
                throw new \LogicException('LOB bindings are not supported for the prepared statement with the lazy transaction initialization queries.');
            }

            $this->assertValidUtf8($value);
            [$value, $type] = match (true) {
                PDO::PARAM_BOOL === $type && (\is_bool($value) || \is_int($value)) => [$value ? 't' : 'f', PDO::PARAM_STR],
                PDO::PARAM_BOOL === $type, PDO::PARAM_INT === $type => [$value, PDO::PARAM_STR],
                default => [$value, $type],
            };
        }

        return parent::bindValue($param, $value, $type);
    }

    #[\Override]
    public function execute(?array $params = null): bool
    {
        /** @var PostgresqlEnhancedPdoConnection $connection */
        $connection = $this->connection->get();
        $isNextLazyTransactionStatement = $connection->lazyTransaction instanceof WeakReference && $connection->lazyTransaction->get() === $this;

        if ($connection->lazyTransaction instanceof WeakReference && !$isNextLazyTransactionStatement) {
            throw new RuntimeException('The prepared statement with the lazy transaction initialization queries has not executed yet.');
        }
        if ($this->startsLazyTransaction && $this->hasExecuted) {
            throw new RuntimeException('A prepared statement with lazy transaction initialization queries can only be executed once.');
        }
        if ($this->startsLazyTransaction && !$isNextLazyTransactionStatement) {
            throw new RuntimeException('A prepared statement with lazy transaction initialization queries can only be executed when it is expected to run.');
        }

        if ($this->startsLazyTransaction) {
            foreach ($params ?? [] as $value) {
                $this->assertValidUtf8($value);
            }

            $this->hasExecuted = true;
            $connection->lazyTransaction = false;
            $connection->broadcastExecuteQuery($this->queryString);

            try {
                return parent::execute($params);
            } catch (PDOException $e) {
                if ('42601' === $e->getCode()) {
                    // A syntax error rejected the whole merged query, not even `BEGIN` was executed. Executing the
                    // queries one by one throws the exception for the failing query and leaves the transaction in the
                    // state it would have been without merged queries.
                    foreach (\array_slice($this->lazyTransactionQueries, 0, -1) as $query) {
                        $connection->exec($query);
                    }

                    // A real prepared statement reports the syntax error when preparing the query, so the values are never
                    // needed. Emulated prepares would embed the unbound placeholders and report a different error.
                    $connection->prepare($this->lazyTransactionQueries[array_key_last($this->lazyTransactionQueries)], [PDO::ATTR_EMULATE_PREPARES => false])->execute();
                }

                throw LazyTransactionLostConnectionException::wrapIfLostConnection($e, \array_slice($this->lazyTransactionQueries, 0, -1));
            }
        }

        // A statement prepared before the lazy transaction started would lose the queued queries. So execute them now.
        $connection->executeQueuedQueriesAndStopQueuing();
        $connection->broadcastExecuteQuery($this->queryString);

        return parent::execute($params);
    }

    private function assertValidUtf8(mixed $value): void
    {
        // Invalid UTF-8 strings can't be quoted for emulated prepares, PDO would silently fail the execution instead of
        // throwing an error. As PostgreSQL would also fail on invalid UTF-8 strings, it is ok to throw there too.
        if (\is_string($value) && 1 !== preg_match('//u', $value)) {
            throw new InvalidArgumentException('The bound value is not a valid UTF-8 string.');
        }
    }
}
