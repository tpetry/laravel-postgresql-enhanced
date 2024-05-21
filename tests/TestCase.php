<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests;

use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as Orchestra;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedServiceProvider;

class TestCase extends Orchestra
{
    use DatabaseTransactions;

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'pgsql');
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
