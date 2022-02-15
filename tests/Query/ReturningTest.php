<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Illuminate\Support\Collection;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );
            CREATE UNIQUE INDEX example_str ON example (str);
        ');
    }

    public function testDeleteReturningAll(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'TmBZCdqd']);
        $this->getConnection()->table('example')->insert(['str' => 'BK5tSuQM']);

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->where('str', 'TmBZCdqd')
                ->deleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'TmBZCdqd']], $result->toArray());
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testDeleteReturningEmpty(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'COsfIVwd']);
        $this->getConnection()->table('example')->insert(['str' => 'NM8gm97z']);

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->where('str', 'JT5z0MzE')
                ->deleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testDeleteReturningSelection(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'FauLvDe6']);
        $this->getConnection()->table('example')->insert(['str' => 'TeYpcH5p']);

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->where('str', 'FauLvDe6')
                ->deleteReturning(returning: ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'FauLvDe6']], $result->toArray());
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning(['str' => 'XPErEjS0']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'XPErEjS0']], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning *'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning([]);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningIgnored(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'Ys3bMnVE']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning(['str' => 'Ys3bMnVE']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning *'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertOrIgnoreReturning(['str' => 'HcuKu7e8'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'HcuKu7e8']], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertReturning(['str' => 'FVKHo1ne']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'FVKHo1ne']], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning *'], array_column($queries, 'query'));
    }

    public function testInsertReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertReturning([]);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testInsertReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertReturning(['str' => 'RFqWlxkC'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'RFqWlxkC']], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertUsingReturning(['str'], "select 'AbsQM4kp'");

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'AbsQM4kp']], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") select \'AbsQM4kp\' returning *'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertUsingReturning(['str'], 'select 1 where 0 = 1');

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") select 1 where 0 = 1 returning *'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->insertUsingReturning(['str'], "select 'EXySSrPj'", ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'EXySSrPj']], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") select \'EXySSrPj\' returning "str"'], array_column($queries, 'query'));
    }

    public function testUpdateFromReturningEmpty(): void
    {
        if (version_compare($this->app->version(), '8.65.0', '<')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()->query()
                ->from('example')
                ->join('example as example2', 'example.id', 'example2.id')
                ->where('example2.str', 'A6eFZk5f')
                ->updateFromReturning(['str' => 'Im0vLxOg']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['update "example" set "str" = ? from "example" as "example2" where "example2"."str" = ? and "example"."id" = "example2"."id" returning *'], array_column($queries, 'query'));
    }

    public function testUpdateFromReturningSelection(): void
    {
        if (version_compare($this->app->version(), '8.65.0', '<')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $this->getConnection()->table('example')->insert(['str' => 'HlmJGJuP']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()->query()
                ->from('example')
                ->join('example as example2', 'example.id', 'example2.id')
                ->where('example2.str', 'HlmJGJuP')
                ->updateFromReturning(['str' => 'Jq27Xlsy'], ['example.str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'Jq27Xlsy']], $result->toArray());
        });
        $this->assertEquals(['update "example" set "str" = ? from "example" as "example2" where "example2"."str" = ? and "example"."id" = "example2"."id" returning "example"."str"'], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningInsertAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['str' => 'XMe8AEva']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'XMe8AEva']], $result->toArray());
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("str" = ?)) as "exists"',
            'insert into "example" ("str") values (?) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningInsertSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['str' => 'APck8iod'], returning: ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'APck8iod']], $result->toArray());
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("str" = ?)) as "exists"',
            'insert into "example" ("str") values (?) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateAll(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'AmsIcAq1']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['id' => 1], ['str' => 'IxCxpIB0']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'IxCxpIB0']], $result->toArray());
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("id" = ?)) as "exists"',
            'update "example" set "str" = ? where "ctid" in (select "example"."ctid" from "example" where ("id" = ?) limit 1) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateEmpty(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'CeHQxTOx']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['id' => 1]);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("id" = ?)) as "exists"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateSelection(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'LlBpLYXh']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateOrInsertReturning(['id' => 1], ['str' => 'NoVyrAHi'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'NoVyrAHi']], $result->toArray());
        });
        $this->assertEquals([
            'select exists(select * from "example" where ("id" = ?)) as "exists"',
            'update "example" set "str" = ? where "ctid" in (select "example"."ctid" from "example" where ("id" = ?) limit 1) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateReturningAll(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'FawRBxNc']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateReturning(['str' => 'A6eFZk5f']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['id' => 1, 'str' => 'A6eFZk5f']], $result->toArray());
        });
        $this->assertEquals(['update "example" set "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testUpdateReturningSelection(): void
    {
        $this->getConnection()->table('example')->insert(['str' => 'HlmJGJuP']);
        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->updateReturning(['str' => 'LUlub1Ta'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([(object) ['str' => 'LUlub1Ta']], $result->toArray());
        });
        $this->assertEquals(['update "example" set "str" = ? returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'Dm2zecf4'], ['str' => 'P0ttyoss']], ['str'], []);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([
                (object) ['id' => 1, 'str' => 'Dm2zecf4'],
                (object) ['id' => 2, 'str' => 'P0ttyoss'],
            ], $result->toArray());
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertSelection(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'KAaNsEnm'], ['str' => 'Hw2i45Ml']], ['str'], [], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([
                (object) ['str' => 'KAaNsEnm'],
                (object) ['str' => 'Hw2i45Ml'],
            ], $result->toArray());
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'KlBTohfj'], ['str' => 'L6dgtF5Y']], ['str'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([
                (object) ['id' => 1, 'str' => 'KlBTohfj'],
                (object) ['id' => 2, 'str' => 'L6dgtF5Y'],
            ], $result->toArray());
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) on conflict ("str") do update set "str" = "excluded"."str" returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertSelection(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertReturning([['str' => 'PXC4tW9x'], ['str' => 'R04o7y3i']], ['str'], ['str'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([
                (object) ['str' => 'PXC4tW9x'],
                (object) ['str' => 'R04o7y3i'],
            ], $result->toArray());
        });
        $this->assertEquals([
            'insert into "example" ("str") values (?), (?) on conflict ("str") do update set "str" = "excluded"."str" returning "str"',
        ], array_column($queries, 'query'));
    }
}
