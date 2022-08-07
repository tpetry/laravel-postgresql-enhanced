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
        Schema::createFunction('calculate_plpgsql_sum', [
            'p_first' => 'int',
            'p_second' => 'int',
        ], 'int', 'BEGIN return p_first + p_second; END');

        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('calculate_plpgsql_sum');
        });
        $this->assertEquals(['DROP FUNCTION calculate_plpgsql_sum'], array_column($queries, 'query'));
    }
}
