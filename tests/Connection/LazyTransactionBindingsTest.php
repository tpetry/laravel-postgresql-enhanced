<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Tpetry\PostgresqlEnhanced\PostgresqlEnhanced;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhancedPdoConnection;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class LazyTransactionBindingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('DROP TABLE IF EXISTS example_lazy_bindings');
        $this->getConnection()->unprepared('CREATE TABLE example_lazy_bindings (blob_col bytea, flag boolean)');
    }

    protected function tearDown(): void
    {
        $this->getConnection()->unprepared('DROP TABLE IF EXISTS example_lazy_bindings');

        parent::tearDown();
    }

    public function testBinaryStringAfterQueuedSetLocalFlushesQueueFirst(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        PostgresqlEnhanced::detectBinaryStrings();
        $payload = "a\0b\xff\x00c";

        $queries = $this->withExecutedQueries(function () use ($payload): void {
            $this->getConnection()->transactionLazy(function () use ($payload): void {
                $this->getConnection()->unprepared('set local app.random = 381203');
                $this->getConnection()->table('example_lazy_bindings')->insert(['blob_col' => $payload]);
            });
        });

        $this->assertSame([
            "begin\n;set local app.random = 381203",
            'insert into "example_lazy_bindings" ("blob_col") values (?)',
            'commit',
        ], array_column($queries['pdo'], 'query'));

        $blob = $this->getConnection()->table('example_lazy_bindings')->value('blob_col');
        $this->assertSame($payload, \is_resource($blob) ? stream_get_contents($blob) : $blob);
    }

    public function testBinaryStringAsFirstLazyStatementStartsTransactionSeparately(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        PostgresqlEnhanced::detectBinaryStrings();
        $payload = "a\0b\xff\x00c";

        $queries = $this->withExecutedQueries(function () use ($payload): void {
            $this->getConnection()->transactionLazy(function () use ($payload): void {
                $this->getConnection()->table('example_lazy_bindings')->insert(['blob_col' => $payload]);
            });
        });

        $this->assertSame(['begin', 'insert into "example_lazy_bindings" ("blob_col") values (?)', 'commit'], array_column($queries['laravel'], 'query'));
        // The transaction is started on its own instead of being merged into the insert statement.
        $this->assertSame(['begin', 'insert into "example_lazy_bindings" ("blob_col") values (?)', 'commit'], array_column($queries['pdo'], 'query'));

        $blob = $this->getConnection()->table('example_lazy_bindings')->value('blob_col');
        $this->assertSame($payload, \is_resource($blob) ? stream_get_contents($blob) : $blob);
    }

    public function testNativeBooleanAsFirstLazyStatementIsStored(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        PostgresqlEnhanced::useNativeBooleans();

        $this->getConnection()->transactionLazy(function (): void {
            $this->getConnection()->table('example_lazy_bindings')->insert(['flag' => true]);
        });

        $this->assertTrue($this->getConnection()->table('example_lazy_bindings')->value('flag'));
    }

    public function testStreamAsFirstLazyStatementIsStoredWhenEnabled(): void
    {
        if (!$this->getConnection()->getPdo() instanceof PostgresqlEnhancedPdoConnection) {
            $this->markTestSkipped('Lazy transactions are not supported in this environment.');
        }

        PostgresqlEnhanced::detectBinaryStrings();
        $payload = 'valid utf-8 \\x41 without nul';
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $payload);
        rewind($stream);

        $queries = $this->withExecutedQueries(function () use ($stream): void {
            $this->getConnection()->transactionLazy(function () use ($stream): void {
                $this->getConnection()->table('example_lazy_bindings')->insert(['blob_col' => $stream]);
            });
        });

        // The transaction is started on its own instead of being merged into the insert statement.
        $this->assertSame(['begin', 'insert into "example_lazy_bindings" ("blob_col") values (?)', 'commit'], array_column($queries['pdo'], 'query'));

        $blob = $this->getConnection()->table('example_lazy_bindings')->value('blob_col');
        $this->assertSame($payload, \is_resource($blob) ? stream_get_contents($blob) : $blob);
    }

    protected function connectionsToTransact(): array
    {
        return [];
    }
}
