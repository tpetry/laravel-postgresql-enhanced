<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class FunctionTest extends TestCase
{
    public function testCreateFunction(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('calculate_plpgsql_sum', [
                'p_first' => 'int',
                'p_second' => 'int',
            ], 'int', 'BEGIN return p_first + p_second; END');

            Schema::createFunction('calculate_sql_sum', [
                'p_first' => 'int',
                'p_second' => 'int',
            ], 'int', 'SELECT p_first + p_second', [
                'language' => 'sql',
                'parallel' => 'safe',
                'leakproof' => false,
                'mutability' => 'stable',
                'cost' => '1',
            ]);
        });
        $this->assertEquals([
            'CREATE FUNCTION calculate_plpgsql_sum(p_first int, p_second int) RETURNS int AS $$ BEGIN return p_first + p_second; END $$ LANGUAGE plpgsql',
            'CREATE FUNCTION calculate_sql_sum(p_first int, p_second int) RETURNS int AS $$ SELECT p_first + p_second $$ LANGUAGE sql PARALLEL safe NOT LEAKPROOF stable COST 1',
        ], array_column($queries, 'query'));
    }

    public function testCreateOrReplaceFunction(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createOrReplaceFunction('calculate_plpgsql_sum', [
                'p_first' => 'int',
                'p_second' => 'int',
            ], 'int', 'BEGIN return p_first + p_second; END');

            Schema::createOrReplaceFunction('calculate_sql_sum', [
                'p_first' => 'int',
                'p_second' => 'int',
            ], 'int', 'SELECT p_first + p_second', [
                'language' => 'sql',
                'parallel' => 'safe',
                'leakproof' => false,
                'mutability' => 'stable',
                'cost' => '1',
            ]);
        });
        $this->assertEquals([
            'CREATE OR REPLACE FUNCTION calculate_plpgsql_sum(p_first int, p_second int) RETURNS int AS $$ BEGIN return p_first + p_second; END $$ LANGUAGE plpgsql',
            'CREATE OR REPLACE FUNCTION calculate_sql_sum(p_first int, p_second int) RETURNS int AS $$ SELECT p_first + p_second $$ LANGUAGE sql PARALLEL safe NOT LEAKPROOF stable COST 1',
        ], array_column($queries, 'query'));
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
