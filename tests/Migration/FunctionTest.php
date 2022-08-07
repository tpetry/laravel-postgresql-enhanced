<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class FunctionTest extends TestCase
{
    public function testCreateFunctionLanguagePlpgsql(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_666644', ['p700746' => 'int'], 'int', 'plpgsql', 'begin select abs(p700746);end');
        });
        $this->assertEquals(['create function "test_666644"("p700746" int) returns int language plpgsql as $$ begin select abs(p700746);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSql(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') < 0) {
            $this->markTestSkipped('SQL function bodies are first supported with PostgreSQL 14.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_515491', ['p903046' => 'int'], 'int', 'sql', 'select abs(p903046)');
        });
        $this->assertEquals(['create function "test_515491"("p903046" int) returns int language sql begin atomic; select abs(p903046); end'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSqlExpression(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') < 0) {
            $this->markTestSkipped('SQL function bodies are first supported with PostgreSQL 14.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_892788', ['p436580' => 'int'], 'int', 'sql:expression', 'abs(p436580)');
        });
        $this->assertEquals(['create function "test_892788"("p436580" int) returns int language sql return (abs(p436580))'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSqlExpressionPg13(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') >= 0) {
            $this->markTestSkipped('SQL function bodies are supported with PostgreSQL 14 and will be preferred.');
        }

        $this->markTestSkipped('TODO: implement');
    }

    public function testCreateFunctionLanguageSqlPg13(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') >= 0) {
            $this->markTestSkipped('SQL function bodies are supported with PostgreSQL 14 and will be preferred.');
        }

        $this->markTestSkipped('TODO: implement');
    }

    public function testDropFunction(): void
    {
        $this->getConnection()->statement("create function test_151316(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('test_151316');
        });
        $this->assertEquals(['drop function "test_151316"'], array_column($queries, 'query'));
    }

    public function testDropFunctionIfExists(): void
    {
        $this->getConnection()->statement("create function test_216089(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunctionIfExists('test_216089');
        });
        $this->assertEquals(['drop function if exists "test_216089"'], array_column($queries, 'query'));
    }

    public function testDropFunctionIfExistsWithArguments(): void
    {
        $this->getConnection()->statement("create function test_675622(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunctionIfExists('test_675622', ['integer']);
        });
        $this->assertEquals(['drop function if exists "test_675622"(integer)'], array_column($queries, 'query'));
    }

    public function testDropFunctionIfExistsWithEmptyArguments(): void
    {
        $this->getConnection()->statement("create function test_780129() returns int as 'select 1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunctionIfExists('test_780129', []);
        });
        $this->assertEquals(['drop function if exists "test_780129"()'], array_column($queries, 'query'));
    }

    public function testDropFunctionWithArguments(): void
    {
        $this->getConnection()->statement("create function test_355700(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('test_355700', ['integer']);
        });
        $this->assertEquals(['drop function "test_355700"(integer)'], array_column($queries, 'query'));
    }

    public function testDropFunctionWithEmptyArguments(): void
    {
        $this->getConnection()->statement("create function test_421087() returns int as 'select 1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('test_421087', []);
        });
        $this->assertEquals(['drop function "test_421087"()'], array_column($queries, 'query'));
    }
}
