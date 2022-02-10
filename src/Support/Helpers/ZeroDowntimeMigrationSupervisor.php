<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Helpers;

use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;
use Tpetry\PostgresqlEnhanced\Schema\Concerns\ZeroDowntimeMigration;
use Tpetry\PostgresqlEnhanced\ZeroDowntimeMigrationTimeoutException;

class ZeroDowntimeMigrationSupervisor
{
    /**
     * The connections which already have been added the before execution handler.
     */
    private array $addedBeforeExecutionHandler = [];

    /**
     * Whether the supervisor has been started.
     */
    private bool $isRegistered = false;

    /**
     * The connection used for the current running migration step.
     */
    private ?PostgresEnhancedConnection $migrationConnection = null;

    /**
     * The timestamp when the migration should result in a timeout because it's running too long.
     */
    private ?float $migrationTimeout = null;

    /**
     * The session's value for a maximum idle transaction time.
     *
     * @see https://www.postgresql.org/docs/current/runtime-config-client.html#GUC-IDLE-IN-TRANSACTION-SESSION-TIMEOUT
     */
    private string $pgsqlIdleInTransactionSessionTimeout;

    /**
     * The session's value for a maximum query running time.
     *
     * @see https://www.postgresql.org/docs/current/runtime-config-client.html#GUC-STATEMENT-TIMEOUT
     */
    private string $pgsqlStatementTimeout;

    /**
     * Whether a zero downtime migration is currently running.
     */
    public function isZeroDowntimeMigrationRunning(): bool
    {
        return $this->isRegistered && null !== $this->migrationConnection;
    }

    /**
     * Tear down the supervisor if it has been started.
     */
    public function migrationEnded(MigrationEnded $event): void
    {
        if (!$this->isZeroDowntimeMigrationRunning()) {
            return;
        }

        $this->remainingTimeOrTimeout();
        $this->stop();
    }

    /**
     * Start the supervisor for every suitable migration.
     *
     * @psalm-suppress UndefinedMethod
     */
    public function migrationStarted(MigrationStarted $event): void
    {
        $this->migrationConnection = $this->getZeroDowntimeConnection($event->migration);
        if (null === $this->migrationConnection) {
            return;
        }

        $this->pgsqlStatementTimeout = $this->migrationConnection->selectOne('SHOW statement_timeout')->statement_timeout;
        $this->pgsqlIdleInTransactionSessionTimeout = $this->migrationConnection->selectOne('SHOW idle_in_transaction_session_timeout')->idle_in_transaction_session_timeout;
        $this->migrationTimeout = match ($event->method) {
            'down' => microtime(true) + $event->migration->timeoutDown(),
            'up' => microtime(true) + $event->migration->timeoutUp(),
            default => throw new RuntimeException("Unknown migration method '{$event->method}'"),
        };

        // If a migration has to execute multiple migrations simple adding the before executing handler will result in
        // the handler being added multiple times. The effect would be that for every query which will be executed the
        // handler will be called multiple times and the timeouts being applied multiple times. The migration may spend
        // more time in updating the timeouts than executing real migration changes. So the handler will only be
        // registered once for every connection.
        if (!\in_array($this->migrationConnection, $this->addedBeforeExecutionHandler, true)) {
            $this->migrationConnection->beforeExecuting(fn () => $this->updateMigrationTimeouts());
            $this->addedBeforeExecutionHandler[] = $this->migrationConnection;
        }

        $this->updateMigrationTimeouts();
    }

    /**
     * Start the zero downtime migration supervisor.
     */
    public function start(): void
    {
        if ($this->isRegistered) {
            return;
        }

        Event::listen(MigrationStarted::class, fn (MigrationStarted $event) => $this->migrationStarted($event));
        Event::listen(QueryExecuted::class, fn () => $this->updateMigrationTimeouts());
        Event::listen(MigrationEnded::class, fn (MigrationEnded $event) => $this->migrationEnded($event));
        $this->isRegistered = true;
    }

    /**
     * Stop the zero downtime migration supervisor.
     */
    public function stop(): void
    {
        if (!$this->isZeroDowntimeMigrationRunning()) {
            return;
        }

        $this->migrationTimeout = null;
        try {
            $this->migrationConnection->unprepared("SET statement_timeout = '{$this->pgsqlStatementTimeout}'");
            $this->migrationConnection->unprepared("SET idle_in_transaction_session_timeout = '{$this->pgsqlIdleInTransactionSessionTimeout}'");
        } catch (QueryException $e) {
            // The timeouts are reset when the supervisor is stopped. But in case the timeout already happened the
            // database connection may be closed by PostgreSQL so it's expected that these queries can fail.
        } finally {
            $this->migrationConnection = null;
        }
    }

    /**
     * Get the migration's connection if it is using zero downtime migration.
     */
    private function getZeroDowntimeConnection(Migration $migration): ?PostgresEnhancedConnection
    {
        $usesTrait = \in_array(ZeroDowntimeMigration::class, trait_uses_recursive($migration::class));
        if (!$usesTrait) {
            return null;
        }
        if (!$migration->withinTransaction) {
            throw new RuntimeException('Zero downtime migrations can only be enforced when migration is running within a transaction');
        }

        $connection = DB::connection();
        if ($migration->getConnection()) {
            $connection = DB::connection($migration->getConnection());
        }

        return match ($connection::class) {
            PostgresEnhancedConnection::class => $connection,
            default => null,
        };
    }

    /**
     * Calculate the remaining time or throw timeout exception if time is exceeded.
     */
    private function remainingTimeOrTimeout(): float
    {
        $maxRemainingTime = (int) ($this->migrationTimeout * 1000 - microtime(true) * 1000);
        if ($maxRemainingTime <= 0) {
            throw new ZeroDowntimeMigrationTimeoutException('Zero downtime migration timeout exceeded.');
        }

        return $maxRemainingTime;
    }

    /**
     * The migration timeouts need to updated for every query which will and has run.
     *
     * @psalm-suppress InvalidArgument
     */
    private function updateMigrationTimeouts(): void
    {
        if (null === $this->migrationConnection || null === $this->migrationTimeout) {
            return;
        }

        // Update the PostgreSQL timeouts but set the migrationTimeout for these queries to null otherwise running
        // these queries will again trigger this function resulting in an infinite loop.
        with($this->migrationTimeout, function (float $migrationTimeout): void {
            $maxRemainingTime = $this->remainingTimeOrTimeout();

            $this->migrationTimeout = null;
            $this->migrationConnection->unprepared("SET statement_timeout = '{$maxRemainingTime}ms'");
            $this->migrationConnection->unprepared("SET idle_in_transaction_session_timeout = '{$maxRemainingTime}ms'");
            $this->migrationTimeout = $migrationTimeout;
        });
    }
}
