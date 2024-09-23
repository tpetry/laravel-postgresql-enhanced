<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Closure;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tpetry\PostgresqlEnhanced\Schema\Concerns\ZeroDowntimeMigration;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;
use Tpetry\PostgresqlEnhanced\ZeroDowntimeMigrationTimeoutException;

class ZeroDowntimeMigrationTest extends TestCase
{
    public function testLongRunningApplicationLogicIsCancelled(): void
    {
        $this->expectException(ZeroDowntimeMigrationTimeoutException::class);
        $this->runMigration(fn () => usleep(500_000), new class extends Migration {
            use ZeroDowntimeMigration;
            public $timeout = 0.4;
        });
    }

    public function testLongRunningQueriesAreCancelled(): void
    {
        $this->expectException(ZeroDowntimeMigrationTimeoutException::class);
        $this->runMigration(function (): void {
            $this->app->get('db.connection')->statement("SELECT pg_sleep_for('250 milliseconds')");
            $this->app->get('db.connection')->statement("SELECT pg_sleep_for('250 milliseconds')");
        }, new class extends Migration {
            use ZeroDowntimeMigration;
            public float $timeout = 0.4;
        });
    }

    public function testMigrationNeedsToRunInTransaction(): void
    {
        $this->expectException(RuntimeException::class);
        $this->runMigration(fn () => usleep(1000), new class extends Migration {
            use ZeroDowntimeMigration;
            public $timeout = 0.0;
            public $withinTransaction = false;
        });
    }

    public function testOnlyRunsOnPostgresql(): void
    {
        $this->expectNotToPerformAssertions();
        $this->runMigration(fn () => usleep(1000), new class extends Migration {
            use ZeroDowntimeMigration;

            public $timeout = 0.0;
            protected $connection = 'mysql';
        });
    }

    public function testTimeoutIsResetForEveryMigration(): void
    {
        $this->expectNotToPerformAssertions();
        $this->runMigration(fn () => usleep(25_000), new class extends Migration {
            use ZeroDowntimeMigration;
            public $timeout = 0.04;
        });
        $this->runMigration(fn () => usleep(25_000), new class extends Migration {
            use ZeroDowntimeMigration;
            public $timeout = 0.04;
        });
    }

    public function testTraitNeedsToBeUsedToActivate(): void
    {
        $this->expectNotToPerformAssertions();
        $this->runMigration(fn () => usleep(1000), new class extends Migration {
            public $timeout = 0.0;
        });
    }

    private function runMigration(Closure $function, Migration $migration): void
    {
        try {
            Event::dispatch(MigrationsStarted::class);
            Event::dispatch(new MigrationStarted($migration, 'up'));
            $this->app->get('db.connection')->transaction($function);
            Event::dispatch(new MigrationEnded($migration, 'up'));
            Event::dispatch(MigrationsEnded::class);
        } catch (ZeroDowntimeMigrationTimeoutException $exception) {
            // The idle_in_transaction_session_timeout will close the connection but the testing framework is
            // expecting a running transaction which can be rolled back. So in case the connection is closed it's
            // purged, a new one is created and a transaction is started which can be rolled back by the framework.
            $this->app->get('db')->purge();
            $this->app->get('db.connection')->beginTransaction();

            throw $exception;
        }
    }
}
