<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class GrantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->statement('CREATE USER test_829423');
        $this->getConnection()->statement('CREATE USER test_102837');
        $this->getConnection()->statement('CREATE SCHEMA private');
    }

    public function testGrantConnect(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::grantConnect('test_829423');
        });
        $this->assertEquals(["grant connect on database {$this->getConnection()->getSchemaGrammar()->wrap($this->getConnection()->getDatabaseName())} to \"test_829423\""], array_column($queries, 'query'));
    }

    public function testGrantReadOnly(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadOnly('test_829423');
        });
        $this->assertEquals([
            'grant usage on schema "public" to "test_829423"',
            'grant select on all tables in schema "public" to "test_829423"',
        ], array_column($queries, 'query'));

        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadOnly('test_102837', 'private');
        });
        $this->assertEquals([
            'grant usage on schema "private" to "test_102837"',
            'grant select on all tables in schema "private" to "test_102837"',
        ], array_column($queries, 'query'));
    }

    public function testGrantReadOnlyIncludeFuture(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadOnly('test_829423', includeFuture: true);
        });
        $this->assertEquals([
            'grant usage on schema "public" to "test_829423"',
            'grant select on all tables in schema "public" to "test_829423"',
            'alter default privileges in schema "public" grant select on tables to "test_829423"',
        ], array_column($queries, 'query'));

        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadOnly('test_102837', 'private', includeFuture: true);
        });
        $this->assertEquals([
            'grant usage on schema "private" to "test_102837"',
            'grant select on all tables in schema "private" to "test_102837"',
            'alter default privileges in schema "private" grant select on tables to "test_102837"',
        ], array_column($queries, 'query'));
    }

    public function testGrantReadWrite(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadWrite('test_829423');
        });
        $this->assertEquals([
            'grant usage on schema "public" to "test_829423"',
            'grant select, insert, update, delete on all tables in schema "public" to "test_829423"',
            'grant usage on all sequences in schema "public" to "test_829423"',
        ], array_column($queries, 'query'));

        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadWrite('test_102837', 'private');
        });
        $this->assertEquals([
            'grant usage on schema "private" to "test_102837"',
            'grant select, insert, update, delete on all tables in schema "private" to "test_102837"',
            'grant usage on all sequences in schema "private" to "test_102837"',
        ], array_column($queries, 'query'));
    }

    public function testGrantReadWriteIncludeFuture(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadWrite('test_829423', includeFuture: true);
        });
        $this->assertEquals([
            'grant usage on schema "public" to "test_829423"',
            'grant select, insert, update, delete on all tables in schema "public" to "test_829423"',
            'grant usage on all sequences in schema "public" to "test_829423"',
            'alter default privileges in schema "public" grant select, insert, update, delete on tables to "test_829423"',
            'alter default privileges in schema "public" grant usage on sequences to "test_829423"',
        ], array_column($queries, 'query'));

        $queries = $this->withQueryLog(static function (): void {
            Schema::grantReadWrite('test_102837', 'private', includeFuture: true);
        });
        $this->assertEquals([
            'grant usage on schema "private" to "test_102837"',
            'grant select, insert, update, delete on all tables in schema "private" to "test_102837"',
            'grant usage on all sequences in schema "private" to "test_102837"',
            'alter default privileges in schema "private" grant select, insert, update, delete on tables to "test_102837"',
            'alter default privileges in schema "private" grant usage on sequences to "test_102837"',
        ], array_column($queries, 'query'));
    }
}
