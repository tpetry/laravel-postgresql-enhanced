<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Illuminate\Database\QueryException;
use PDO;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedPdoConnection;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class LazyTransactionTest extends TestCase
{
    public function testBeginLazyFailOnUnsupportedVersions(): void
    {
        if ($this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are supported in this environment.');
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lazy transactions are only supported with PHP 8.4+, Laravel 11+ and non-persistent connections.');

        $this->getConnection()->beginTransactionLazy();
    }

    public function testBeginLazyPrepareStopsQueuing(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransactionLazy();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->unprepared('set local app.random = 581207');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->statement('set local app.random = 149263');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->unprepared('select 1');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->commit();
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
        });

        $this->assertEquals([
            'begin',
            'set local app.random = 581207',
            'set local app.random = 149263',
            'select 1',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.random = 581207\n;set local app.random = 149263",
            'select 1',
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testBeginLazyQueuedSyntaxFailuresDoesRebuildTransactionState(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransactionLazy();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->unprepared('set local invalid-keyword app.random = 638203');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            try {
                $this->getConnection()->unprepared('select 1');
                $this->fail('The former statement should have thrown an exception.');
            } catch (QueryException $e) {
                $this->assertStringContainsString("SQLSTATE[42601]: Syntax error: 7 ERROR:  syntax error at or near \"-\"\nLINE 1: set local invalid-keyword app.random = 638203", $e->getMessage());
            }
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            try {
                $this->getConnection()->unprepared('select 2');
                $this->fail('The former statement should have thrown an exception.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('SQLSTATE[25P02]: In failed sql transaction: 7 ERROR:  current transaction is aborted, commands ignored until end of transaction block', $e->getMessage());
            }
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
        });

        $this->assertEquals([
            'begin',
            'set local invalid-keyword app.random = 638203',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local invalid-keyword app.random = 638203\n;select 1",
            'begin',
            'set local invalid-keyword app.random = 638203',
            'select 2',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testBeginLazyReconnect(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        /** @var PostgresqlEnhancedPdoConnection $pdo */
        $pdo = $this->getConnection()->getPdo();
        $queries = $this->withExecutedQueries(function () use ($pdo): void {
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransactionLazy();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());

            // The connection is killed by another connection after the lazy transaction has been started. A plain PDO
            // connection is used to not interfere with Laravel's connection handling.
            $config = $this->app['config']->get('database.connections.pgsql');
            (new PDO("pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}", $config['username'], $config['password']))
                ->exec("select pg_terminate_backend({$pdo->getPid()}, 5000)");

            $this->getConnection()->unprepared('set local app.random = 706231');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->statement('select 1');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->commit();
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
        });

        $this->assertNotEquals($pdo, $this->getConnection());
        $this->assertEquals([
            'begin',
            'set local app.random = 706231',
            'select 1',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.random = 706231\n;select 1",
            "begin\n;set local app.random = 706231\n;select 1",
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame([$pdo, $this->getConnection()->getPdo(), $this->getConnection()->getPdo()], array_column($queries['pdo'], 'pdo'));
    }

    public function testBeginLazyUnqueableExecStopsQueuing(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransactionLazy();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->unprepared('set local app.random = 820132');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->unprepared('select 1');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->unprepared('select 2');
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->commit();
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
        });

        $this->assertEquals([
            'begin',
            'set local app.random = 820132',
            'select 1',
            'select 2',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.random = 820132\n;select 1",
            'select 2',
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testBeginLazyWithoutRealWorkIsSkipped(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransactionLazy();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransactionLazy();
            $this->assertEquals(2, $this->getConnection()->transactionLevel());
            $this->assertTrue($this->getConnection()->unprepared("set local app.foo = 'bar1'"));
            $this->assertEquals(2, $this->getConnection()->transactionLevel());
            $this->getConnection()->commit();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->beginTransaction();
            $this->assertEquals(2, $this->getConnection()->transactionLevel());
            $this->assertTrue($this->getConnection()->unprepared("set local app.foo = 'bar2'"));
            $this->assertEquals(2, $this->getConnection()->transactionLevel());
            $this->getConnection()->commit();
            $this->assertEquals(1, $this->getConnection()->transactionLevel());
            $this->getConnection()->rollback();
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
        });

        $this->assertEquals([
            'begin',
            'savepoint trans2',
            "set local app.foo = 'bar1'",
            'release savepoint trans2',
            'savepoint trans2',
            "set local app.foo = 'bar2'",
            'release savepoint trans2',
            'rollback',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEmpty($queries['pdo']);
    }

    public function testTransactionLazyFailOnUnsupportedVersions(): void
    {
        if ($this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are supported in this environment.');
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Lazy transactions are only supported with PHP 8.4+, Laravel 11+ and non-persistent connections.');

        $this->getConnection()->transactionLazy(static fn () => null);
    }

    public function testTransactionLazyPrepareStopsQueuing(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->getConnection()->transactionLazy(function () {
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('set local app.random = 108362');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->statement('set local app.random = 72819');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('select 1');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
            });
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
        });

        $this->assertEquals([
            'begin',
            'set local app.random = 108362',
            'set local app.random = 72819',
            'select 1',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.random = 108362\n;set local app.random = 72819",
            'select 1',
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testTransactionLazyQueuedSyntaxFailuresDoesRebuildTransactionState(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->getConnection()->transactionLazy(function () {
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('set local invalid-keyword app.random = 981032');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                try {
                    $this->getConnection()->unprepared('select 1');
                    $this->fail('The former statement should have thrown an exception.');
                } catch (QueryException $e) {
                    $this->assertStringContainsString("SQLSTATE[42601]: Syntax error: 7 ERROR:  syntax error at or near \"-\"\nLINE 1: set local invalid-keyword app.random = 981032", $e->getMessage());
                }
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                try {
                    $this->getConnection()->unprepared('select 2');
                    $this->fail('The former statement should have thrown an exception.');
                } catch (QueryException $e) {
                    $this->assertStringContainsString('SQLSTATE[25P02]: In failed sql transaction: 7 ERROR:  current transaction is aborted, commands ignored until end of transaction block', $e->getMessage());
                }
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
            });
        });

        $this->assertEquals([
            'begin',
            'set local invalid-keyword app.random = 981032',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local invalid-keyword app.random = 981032\n;select 1",
            'begin',
            'set local invalid-keyword app.random = 981032',
            'select 2',
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testTransactionLazyReconnect(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        /** @var PostgresqlEnhancedPdoConnection $pdo */
        $pdo = $this->getConnection()->getPdo();
        $queries = $this->withExecutedQueries(function () use ($pdo): void {
            $this->getConnection()->transactionLazy(function () use ($pdo): void {
                $this->assertEquals(1, $this->getConnection()->transactionLevel());

                // The connection is killed by another connection after the lazy transaction has been started. A plain PDO
                // connection is used to not interfere with Laravel's connection handling.
                $config = $this->app['config']->get('database.connections.pgsql');
                (new PDO("pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}", $config['username'], $config['password']))
                    ->exec("select pg_terminate_backend({$pdo->getPid()}, 5000)");

                $this->getConnection()->unprepared('set local app.random = 402683');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->statement('select 1');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
            });
        });

        $this->assertNotEquals($pdo, $this->getConnection());
        $this->assertEquals([
            'begin',
            'set local app.random = 402683',
            'select 1',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.random = 402683\n;select 1",
            "begin\n;set local app.random = 402683\n;select 1",
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame([$pdo, $this->getConnection()->getPdo(), $this->getConnection()->getPdo()], array_column($queries['pdo'], 'pdo'));
    }

    public function testTransactionLazyRetriesAreLazy(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        /** @var int $attempt */
        $attempt = 0;
        $queries = $this->withExecutedQueries(function () use (&$attempt): void {
            $this->getConnection()->transactionLazy(function () use (&$attempt): void {
                ++$attempt;
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared("set local app.attempt = {$attempt}");
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('set local app.random = 820313');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->statement('select 1');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->statement('select 2');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());

                throw_if(1 === $attempt, message: 'Simulated failure on the first try: deadlock detected.');
            }, attempts: 2);
            $this->assertEquals(0, $this->getConnection()->transactionLevel());
        });

        $this->assertEquals(2, $attempt);
        $this->assertEquals([
            'begin',
            'set local app.attempt = 1',
            'set local app.random = 820313',
            'select 1',
            'select 2',
            'rollback',
            'begin',
            'set local app.attempt = 2',
            'set local app.random = 820313',
            'select 1',
            'select 2',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.attempt = 1\n;set local app.random = 820313\n;select 1",
            'select 2',
            'rollback',
            "begin\n;set local app.attempt = 2\n;set local app.random = 820313\n;select 1",
            'select 2',
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testTransactionLazyUnqueableExecStopsQueuing(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->getConnection()->transactionLazy(function () {
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('set local app.random = 820212');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('select 1');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->getConnection()->unprepared('select 2');
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
            });
        });

        $this->assertEquals([
            'begin',
            'set local app.random = 820212',
            'select 1',
            'select 2',
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEquals([
            "begin\n;set local app.random = 820212\n;select 1",
            'select 2',
            'commit',
        ], array_column($queries['pdo'], 'query'));
        $this->assertSame(array_fill(0, \count($queries['pdo']), $this->getConnection()->getPdo()), array_column($queries['pdo'], 'pdo'));
    }

    public function testTransactionLazyWithoutRealWorkIsSkipped(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        $queries = $this->withExecutedQueries(function () {
            $this->getConnection()->transactionLazy(function () {
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->assertTrue($this->getConnection()->unprepared("set local app.foo = 'bar1'"));
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
                $this->assertTrue($this->getConnection()->unprepared("set local app.foo = 'bar2'"));
                $this->assertEquals(1, $this->getConnection()->transactionLevel());
            });
        });

        $this->assertEquals([
            'begin',
            "set local app.foo = 'bar1'",
            "set local app.foo = 'bar2'",
            'commit',
        ], array_column($queries['laravel'], 'query'));
        $this->assertEmpty($queries['pdo']);
    }

    protected function connectionsToTransact(): array
    {
        return [];
    }
}
