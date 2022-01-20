<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests;

use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Orchestra\Testbench\TestCase as Orchestra;
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

    protected function getPackageProviders($app)
    {
        return [
            PostgresqlEnhancedServiceProvider::class,
        ];
    }

    protected function withQueryLog(Closure $fn): array
    {
        $this->app->get('db.connection')->flushQueryLog();
        $this->app->get('db.connection')->enableQueryLog();
        $fn();

        return $this->app->get('db.connection')->getQueryLog();
    }
}
