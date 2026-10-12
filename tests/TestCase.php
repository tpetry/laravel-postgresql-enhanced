<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as Orchestra;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedPdoConnection;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedServiceProvider;

class TestCase extends Orchestra
{
    use DatabaseTransactions;

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql.search_path', null);
    }

    protected function connectionsToTransact(): array
    {
        return ['pgsql'];
    }

    /**
     * Get the database connection.
     */
    protected function getConnection($connection = null, $table = null): PostgresEnhancedConnection
    {
        /** @var PostgresEnhancedConnection $connection */
        $connection = parent::getConnection($connection, $table);

        return $connection;
    }

    protected function getPackageProviders($app)
    {
        return [
            PostgresqlEnhancedServiceProvider::class,
        ];
    }

    protected function withExecutedQueries(Closure $fn): array
    {
        $queriesLaravel = [];
        $this->getConnection()->listen(static function (QueryExecuted $query) use (&$queriesLaravel) {
            $queriesLaravel[] = [
                'query' => $query->sql,
                'bindings' => $query->bindings,
            ];
        });
        $this->app['events']->listen(TransactionBeginning::class, static function (TransactionBeginning $event) use (&$queriesLaravel) {
            $queriesLaravel[] = [
                'query' => with($event->connection->transactionLevel(), static fn ($level) => 1 === $level ? 'begin' : "savepoint trans{$level}"),
                'bindings' => [],
            ];
        });
        $this->app['events']->listen(TransactionCommitted::class, static function (TransactionCommitted $event) use (&$queriesLaravel) {
            $queriesLaravel[] = [
                'query' => with($event->connection->transactionLevel(), static fn ($level) => 0 === $level ? 'commit' : \sprintf('release savepoint trans%d', $level + 1)),
                'bindings' => [],
            ];
        });
        $this->app['events']->listen(TransactionRolledBack::class, static function (TransactionRolledBack $event) use (&$queriesLaravel) {
            $queriesLaravel[] = [
                'query' => with($event->connection->transactionLevel(), static fn ($level) => 0 === $level ? 'rollback' : \sprintf('rollabck to savepoint trans%d', $level + 1)),
                'bindings' => [],
            ];
        });

        $queriesPdo = [];
        PostgresqlEnhancedPdoConnection::listen(static function (string $query, PostgresqlEnhancedPdoConnection $pdo) use (&$queriesPdo) {
            $queriesPdo[] = compact('pdo', 'query');
        });

        try {
            $fn();
        } finally {
            // The listener would keep every following PDO connection alive by storing it in $queriesPdo.
            PostgresqlEnhancedPdoConnection::flushListeners();
        }

        return [
            'laravel' => $queriesLaravel,
            'pdo' => $queriesPdo,
        ];
    }

    protected function withQueryLog(Closure $fn, bool $pretend = false): array
    {
        $this->getConnection()->flushQueryLog();
        $this->getConnection()->enableQueryLog();

        match ($pretend) {
            true => $this->getConnection()->pretend($fn),
            false => $fn(),
        };

        return $this->getConnection()->getQueryLog();
    }
}
