<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Connection;

use Closure;
use Illuminate\Container\Container;
use Throwable;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedPdoConnection;
use Tpetry\PostgresqlEnhanced\Support\Helpers\ZeroDowntimeMigrationSupervisor;
use Tpetry\PostgresqlEnhanced\ZeroDowntimeMigrationTimeoutException;

trait Transaction
{
    /**
     * Whether the next started transaction should be lazy.
     */
    private bool $beginsLazyTransaction = false;

    public function beginTransactionLazy(): void
    {
        $this->withLazyTransaction(fn () => $this->beginTransaction());
    }

    /**
     * @template TReturn
     *
     * @param Closure(): TReturn $callback
     *
     * @return TReturn
     */
    public function transactionLazy(Closure $callback, int $attempts = 1): mixed
    {
        return $this->withLazyTransaction(fn () => $this->transaction($callback, $attempts));
    }

    #[\Override]
    protected function createTransaction(): void
    {
        if ($this->beginsLazyTransaction && 0 === $this->transactionLevel()) {
            $this->reconnectIfMissingConnection();
            $pdo = $this->getPdo();
            if (!$pdo instanceof PostgresqlEnhancedPdoConnection) {
                throw new \RuntimeException('Lazy transactions are only supported with PHP 8.4+, Laravel 11+ and non-persistent connections.');
            }
            $pdo->useLazyTransactionForNextBegin();
        }

        parent::createTransaction();
    }

    /**
     * Handle an exception encountered when running a transacted statement.
     *
     * @param Throwable $e
     * @param int $currentAttempt
     * @param int $maxAttempts
     */
    #[\Override]
    protected function handleTransactionException($e, $currentAttempt, $maxAttempts): void
    {
        try {
            parent::handleTransactionException($e, $currentAttempt, $maxAttempts);
        } catch (ZeroDowntimeMigrationTimeoutException $e) {
            Container::getInstance()->get(ZeroDowntimeMigrationSupervisor::class)->stop();

            // The laravel handleTransactionException handler will try to rollback any changes done in the failed
            // transaction. In case on a ZeroDowntimeMigrationTimeoutException the database connection will
            // probably already be closed so trying to rollback would throw new exception. When the $handleException
            // is thrown all operations of the transaction have been reversed and the timeout exception can be
            // thrown again.
            throw $e;
        }
    }

    /**
     * @template TReturn
     *
     * @param Closure(): TReturn $callback
     *
     * @return TReturn
     */
    private function withLazyTransaction(Closure $callback): mixed
    {
        $current = $this->beginsLazyTransaction;
        $this->beginsLazyTransaction = true;

        try {
            return $callback();
        } finally {
            $this->beginsLazyTransaction = $current;
        }
    }
}
