<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class LateralJoinTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example1 (
                example1_id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                category bigint NOT NULL,
                max_value bigint NOT NULL
            );
            CREATE TABLE example2 (
                example2_id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                category bigint NOT NULL,
                value bigint NOT NULL
            )
        ');
    }

    public function testCrossJoinSubLateral(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example1')
                ->crossJoinSubLateral(DB::table('example2')->whereColumn('example2.category', 'example1.category')->whereColumn('example2.value', '<=', 'example1.max_value')->limit(3), 'example2')
                ->get();
        });
        $this->assertEquals(
            ['select * from "example1" cross join lateral (select * from "example2" where "example2"."category" = "example1"."category" and "example2"."value" <= "example1"."max_value" limit 3) as "example2"'],
            array_column($queries, 'query'),
        );
    }

    public function testJoinSubLateral(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example1')
                ->joinSubLateral(DB::table('example2')->whereColumn('example2.value', '<=', 'example1.max_value')->limit(3), 'example2', 'example2.category', 'example1.category')
                ->get();
        });
        $this->assertEquals(
            ['select * from "example1" inner join lateral (select * from "example2" where "example2"."value" <= "example1"."max_value" limit 3) as "example2" on "example2"."category" = "example1"."category"'],
            array_column($queries, 'query'),
        );
    }

    public function testLeftJoinSubLateral(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example1')
                ->leftJoinSubLateral(DB::table('example2')->whereColumn('example2.value', '<=', 'example1.max_value')->limit(3), 'example2', 'example2.category', 'example1.category')
                ->get();
        });
        $this->assertEquals(
            ['select * from "example1" left join lateral (select * from "example2" where "example2"."value" <= "example1"."max_value" limit 3) as "example2" on "example2"."category" = "example1"."category"'],
            array_column($queries, 'query'),
        );
    }
}
