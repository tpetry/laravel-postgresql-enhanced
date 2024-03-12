<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Composer\Semver\Comparator;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class CteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL UNIQUE
            );
        ');
    }

    public function testQueryDelete(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereIn('id', $this->getConnection()->table('cte')->select('id'))
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'Uk6EuB4g'))
                ->where('example.id', '>', 0)
                ->delete();
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) delete from "example" where "id" in (select "id" from "cte") and "example"."id" > ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['Uk6EuB4g', 0]], array_column($queries, 'bindings'));
    }

    public function testQueryDeleteReturning(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereIn('id', $this->getConnection()->table('cte')->select('id'))
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'IXV0EWT4'))
                ->where('example.id', '>', 0)
                ->deleteReturning();
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) delete from "example" where "id" in (select "id" from "cte") and "example"."id" > ? returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['IXV0EWT4', 0]], array_column($queries, 'bindings'));
    }

    public function testQueryInsert(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'WkG3Z2pO'))
                ->insert(['str' => 'D3JMPKxB']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?)'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['WkG3Z2pO', 'D3JMPKxB']], array_column($queries, 'bindings'));
    }

    public function testQueryInsertGetId(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'MTwvOvur'))
                ->insertGetId(['str' => 'K2S3NjJ6']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?) returning "id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['MTwvOvur', 'K2S3NjJ6']], array_column($queries, 'bindings'));
    }

    public function testQueryInsertOrIgnore(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'EO3eCxMS'))
                ->insertOrIgnore(['str' => 'B7oHOFMI']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?) on conflict do nothing'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['EO3eCxMS', 'B7oHOFMI']], array_column($queries, 'bindings'));
    }

    public function testQueryInsertOrIgnoreReturning(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'H7jUbNJC'))
                ->insertOrIgnoreReturning(['str' => 'EfOFyWol']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?) on conflict do nothing returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['H7jUbNJC', 'EfOFyWol']], array_column($queries, 'bindings'));
    }

    public function testQueryInsertReturning(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'GlDlEMEm'))
                ->insertReturning(['str' => 'UuEETL18']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?) returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['GlDlEMEm', 'UuEETL18']], array_column($queries, 'bindings'));
    }

    public function testQueryInsertUsing(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'NYyITVNZ'))
                ->insertUsing(['str'], $this->getConnection()->table('cte')->select('str')->where('id', '>', 0));
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") select "str" from "cte" where "id" > ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['NYyITVNZ', 0]], array_column($queries, 'bindings'));
    }

    public function testQueryInsertUsingReturning(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'S5EOLNFg'))
                ->insertUsingReturning(['str'], $this->getConnection()->table('cte')->select('str')->where('id', '>', 0));
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") select "str" from "cte" where "id" > ? returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['S5EOLNFg', 0]], array_column($queries, 'bindings'));
    }

    public function testQuerySelect(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'FnkjFQeF'))
                ->join('cte', 'example.id', 'cte.id')
                ->where('example.id', '>', 0)
                ->get();
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) select * from "example" inner join "cte" on "example"."id" = "cte"."id" where "example"."id" > ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['FnkjFQeF', 0]], array_column($queries, 'bindings'));
    }

    public function testQueryUpdate(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'YSgjuNr9'))
                ->update(['str' => 'Ftz19bI2']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) update "example" set "str" = ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['YSgjuNr9', 'Ftz19bI2']], array_column($queries, 'bindings'));
    }

    public function testQueryUpdateFrom(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.65.0')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->join('cte', 'cte.id', 'example.id')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'BYMLhU7o'))
                ->updateFrom(['str' => 'QFOjZNak']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) update "example" set "str" = ? from "cte" where "cte"."id" = "example"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['BYMLhU7o', 'QFOjZNak']], array_column($queries, 'bindings'));
    }

    public function testQueryUpdateFromReturning(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.65.0')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->join('cte', 'cte.id', 'example.id')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'OYQjV3UN'))
                ->updateFromReturning(['str' => 'A0r4ltA4']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) update "example" set "str" = ? from "cte" where "cte"."id" = "example"."id" returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['OYQjV3UN', 'A0r4ltA4']], array_column($queries, 'bindings'));
    }

    public function testQueryUpdateReturning(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'JstUfedp'))
                ->updateReturning(['str' => 'Rowvn5Pn']);
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) update "example" set "str" = ? returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['JstUfedp', 'Rowvn5Pn']], array_column($queries, 'bindings'));
    }

    public function testQueryUpsert(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.10.0')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'EmM8BuKm'))
                ->upsert([['str' => 'ZnQf0H7A']], 'str');
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?) on conflict ("str") do update set "str" = "excluded"."str"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['EmM8BuKm', 'ZnQf0H7A']], array_column($queries, 'bindings'));
    }

    public function testQueryUpsertReturning(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.10.0')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'B4loEL3F'))
                ->upsertReturning([['str' => 'VoGHW4Qm']], 'str');
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) insert into "example" ("str") values (?) on conflict ("str") do update set "str" = "excluded"."str" returning *'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['B4loEL3F', 'VoGHW4Qm']], array_column($queries, 'bindings'));
    }

    public function testSyntaxAll(): void
    {
        // The recursive query doesn't make any sense! But it's the simplest one I can imagine to just the correct query building.
        $queries = $this->withQueryLog(function (): void {
            $cteQuery = $this->getConnection()->table('example')->where('str', 'Xu8ZFfno')->unionAll(
                $this->getConnection()->table('cte')->where('str', '!=', 'Xu8ZFfno')
            );

            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $cteQuery, [
                    'materialized' => true,
                    'recursive' => true,
                    'cycle' => 'id set is_cycle using path',
                    'search' => 'breadth first by id SET str2',
                ])
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with recursive "cte" as materialized ((select * from "example" where "str" = ?) union all (select * from "cte" where "str" != ?)) search breadth first by id SET str2 cycle id set is_cycle using path select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['Xu8ZFfno', 'Xu8ZFfno']], array_column($queries, 'bindings'));
    }

    public function testSyntaxMaterialized(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'RWxjwwlF'), [
                    'materialized' => true,
                ])
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with "cte" as materialized (select * from "example" where "str" = ?) select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['RWxjwwlF']], array_column($queries, 'bindings'));
    }

    public function testSyntaxRecursive(): void
    {
        // The recursive query doesn't make any sense! But it's the simplest one I can imagine to just the correct query building.
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'HJFMCVmj'), [
                    'recursive' => true,
                ])
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with recursive "cte" as (select * from "example" where "str" = ?) select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['HJFMCVmj']], array_column($queries, 'bindings'));
    }

    public function testSyntaxRecursiveCycle(): void
    {
        // The recursive query doesn't make any sense! But it's the simplest one I can imagine to just the correct query building.
        $queries = $this->withQueryLog(function (): void {
            $cteQuery = $this->getConnection()->table('example')->where('str', 'KIqJENSm')->unionAll(
                $this->getConnection()->table('cte')->where('str', '!=', 'KIqJENSm')
            );

            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $cteQuery, [
                    'recursive' => true,
                    'cycle' => 'id set is_cycle using path',
                ])
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with recursive "cte" as ((select * from "example" where "str" = ?) union all (select * from "cte" where "str" != ?)) cycle id set is_cycle using path select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['KIqJENSm', 'KIqJENSm']], array_column($queries, 'bindings'));
    }

    public function testSyntaxRecursiveMultiple(): void
    {
        // The recursive query doesn't make any sense! But it's the simplest one I can imagine to just the correct query building.
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte1', $this->getConnection()->table('example')->where('str', 'HgG7LfhI'), [
                    'recursive' => true,
                ])
                ->withExpression('cte2', $this->getConnection()->table('example')->where('str', 'KiaCDOqa'), [
                    'recursive' => true,
                ])
                ->get();
        });

        $this->assertEquals(
            ['with recursive "cte1" as (select * from "example" where "str" = ?), "cte2" as (select * from "example" where "str" = ?) select * from "example"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['HgG7LfhI', 'KiaCDOqa']], array_column($queries, 'bindings'));
    }

    public function testSyntaxRecursiveSearch(): void
    {
        // The recursive query doesn't make any sense! But it's the simplest one I can imagine to just the correct query building.
        $queries = $this->withQueryLog(function (): void {
            $cteQuery = $this->getConnection()->table('example')->where('str', 'UmdXoTb1')->unionAll(
                $this->getConnection()->table('cte')->where('str', '!=', 'UmdXoTb1')
            );

            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $cteQuery, [
                    'recursive' => true,
                    'search' => 'breadth first by id SET str2',
                ])
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with recursive "cte" as ((select * from "example" where "str" = ?) union all (select * from "cte" where "str" != ?)) search breadth first by id SET str2 select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['UmdXoTb1', 'UmdXoTb1']], array_column($queries, 'bindings'));
    }

    public function testSyntaxSameAlias(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte1', $this->getConnection()->table('example')->where('str', 'HH7qHsCr'))
                ->withExpression('cte2', $this->getConnection()->table('example')->where('str', 'M1oRd4NG'))
                ->withExpression('cte1', $this->getConnection()->table('example')->where('str', 'YZwoaSWU'))

                ->get();
        });

        $this->assertEquals(
            ['with "cte2" as (select * from "example" where "str" = ?), "cte1" as (select * from "example" where "str" = ?) select * from "example"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['M1oRd4NG', 'YZwoaSWU']], array_column($queries, 'bindings'));
    }

    public function testSyntaxSimple(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'NDH4TClg'))
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with "cte" as (select * from "example" where "str" = ?) select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['NDH4TClg']], array_column($queries, 'bindings'));
    }

    public function testSyntaxUnmaterialized(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->withExpression('cte', $this->getConnection()->table('example')->where('str', 'Lh1o7vdG'), [
                    'materialized' => false,
                ])
                ->join('cte', 'example.id', 'cte.id')
                ->get();
        });

        $this->assertEquals(
            ['with "cte" as not materialized (select * from "example" where "str" = ?) select * from "example" inner join "cte" on "example"."id" = "cte"."id"'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['Lh1o7vdG']], array_column($queries, 'bindings'));
    }
}
