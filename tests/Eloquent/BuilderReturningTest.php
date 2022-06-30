<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class BuilderReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(); // only compatible way of freezing time in Laravel 6
        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL UNIQUE,
                created_at timestamptz,
                updated_at timestamptz,
                deleted_at timestamptz
            );
        ');
    }

    public function testDeleteReturningAll(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'Adrv9nlq']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->where('str', 'Adrv9nlq')
                ->deleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'Adrv9nlq', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
            $this->assertFalse($result->first()->exists);
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testDeleteReturningAllWithSoftDeletes(): void
    {
        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'HVpFcyZc']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->where('str', 'HVpFcyZc')
                ->deleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'HVpFcyZc', 'created_at' => null, 'updated_at' => now()->getTimestamp(), 'deleted_at' => now()->getTimestamp()]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['update "example" set "deleted_at" = ?, "updated_at" = ? where "str" = ? and "example"."deleted_at" is null returning *'], array_column($queries, 'query'));
    }

    public function testDeleteReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->where('str', 'FmmwcCEa')
                ->deleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testDeleteReturningEmptyWithSoftDeletes(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->where('str', 'KNfGPTun')
                ->deleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['update "example" set "deleted_at" = ?, "updated_at" = ? where "str" = ? and "example"."deleted_at" is null returning *'], array_column($queries, 'query'));
    }

    public function testDeleteReturningSelection(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'ERyX7zrJ']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->where('str', 'ERyX7zrJ')
                ->deleteReturning(returning: ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'ERyX7zrJ']], $result->toArray());
            $this->assertFalse($result->first()->exists);
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning "str"'], array_column($queries, 'query'));
    }

    public function testDeleteReturningSelectionWithSoftDeletes(): void
    {
        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'Uemf1STe']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->where('str', 'Uemf1STe')
                ->deleteReturning(returning: ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'Uemf1STe']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['update "example" set "deleted_at" = ?, "updated_at" = ? where "str" = ? and "example"."deleted_at" is null returning "str"'], array_column($queries, 'query'));
    }

    public function testForceDeleteReturningAll(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'SMxNkHUc']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->where('str', 'SMxNkHUc')
                ->forceDeleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'SMxNkHUc', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
            $this->assertFalse($result->first()->exists);
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testForceDeleteReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->where('str', 'PrLPFJ4s')
                ->forceDeleteReturning();

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testForceDeleteReturningSelection(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'XT4wRUzX']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->where('str', 'XT4wRUzX')
                ->forceDeleteReturning(returning: ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'XT4wRUzX']], $result->toArray());
            $this->assertFalse($result->first()->exists);
        });
        $this->assertEquals(['delete from "example" where "str" = ? returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertOrIgnoreReturning(['str' => 'GfzH1fba']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'GfzH1fba', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning *'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertOrIgnoreReturning([]);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningIgnored(): void
    {
        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'ASWTUBhJ']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertOrIgnoreReturning(['str' => 'ASWTUBhJ']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning *'], array_column($queries, 'query'));
    }

    public function testInsertOrIgnoreReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertOrIgnoreReturning(['str' => 'WHKzRCy3'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'WHKzRCy3']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict do nothing returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertReturning(['str' => 'L55uRXMI']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'L55uRXMI', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning *'], array_column($queries, 'query'));
    }

    public function testInsertReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertReturning([]);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testInsertReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertReturning(['str' => 'HarJvEmz'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'HarJvEmz']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning "str"'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertUsingReturning(['str'], "select 'LOfbaRG4'");

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'LOfbaRG4', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") select \'LOfbaRG4\' returning *'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningEmpty(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertUsingReturning(['str'], 'select 1 where 0 = 1');

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['insert into "example" ("str") select 1 where 0 = 1 returning *'], array_column($queries, 'query'));
    }

    public function testInsertUsingReturningSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->insertUsingReturning(['str'], "select 'CT4TfugQ'", ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'CT4TfugQ']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") select \'CT4TfugQ\' returning "str"'], array_column($queries, 'query'));
    }

    public function testUpdateFromReturningEmpty(): void
    {
        if (version_compare($this->app->version(), '8.65.0', '<')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->join('example as example2', 'example.id', 'example2.id')
                ->where('example2.str', 'Ru9faGcF')
                ->updateFromReturning(['str' => 'Ut1WOyHU']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals(['update "example" set "str" = ? from "example" as "example2" where "example2"."str" = ? and "example"."deleted_at" is null and "example"."id" = "example2"."id" returning *'], array_column($queries, 'query'));
    }

    public function testUpdateFromReturningSelection(): void
    {
        if (version_compare($this->app->version(), '8.65.0', '<')) {
            $this->markTestSkipped('UpdateFrom() has been added in a later Laravel version.');
        }

        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'NkAKoLip']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->join('example as example2', 'example.id', 'example2.id')
                ->where('example2.str', 'NkAKoLip')
                ->updateFromReturning(['str' => 'QqIQInHu'], ['example.str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'QqIQInHu']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['update "example" set "str" = ? from "example" as "example2" where "example2"."str" = ? and "example"."deleted_at" is null and "example"."id" = "example2"."id" returning "example"."str"'], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningInsertAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateOrInsertReturning(['str' => 'SQXSCwPM']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'SQXSCwPM', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals([
            'select exists(select * from "example" where "example"."deleted_at" is null and ("str" = ?)) as "exists"',
            'insert into "example" ("str") values (?) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningInsertSelection(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateOrInsertReturning(['str' => 'MrAom5QZ'], returning: ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'MrAom5QZ']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals([
            'select exists(select * from "example" where "example"."deleted_at" is null and ("str" = ?)) as "exists"',
            'insert into "example" ("str") values (?) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateAll(): void
    {
        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'NEI9yhO2']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateOrInsertReturning(['id' => 1], ['str' => 'FDuHP01C']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'FDuHP01C', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals([
            'select exists(select * from "example" where "example"."deleted_at" is null and ("id" = ?)) as "exists"',
            'update "example" set "str" = ? where "ctid" in (select "example"."ctid" from "example" where "example"."deleted_at" is null and ("id" = ?) limit 1) returning *',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateEmpty(): void
    {
        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'IrwHoXrF']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateOrInsertReturning(['id' => 1]);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([
            'select exists(select * from "example" where "example"."deleted_at" is null and ("id" = ?)) as "exists"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateOrInsertReturningUpdateSelection(): void
    {
        with(new ExampleTimestamps())->newQuery()->insert(['str' => 'GYyLmamo']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateOrInsertReturning(['id' => 1], ['str' => 'ZAnrBoQB'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'ZAnrBoQB']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals([
            'select exists(select * from "example" where "example"."deleted_at" is null and ("id" = ?)) as "exists"',
            'update "example" set "str" = ? where "ctid" in (select "example"."ctid" from "example" where "example"."deleted_at" is null and ("id" = ?) limit 1) returning "str"',
        ], array_column($queries, 'query'));
    }

    public function testUpdateReturningAll(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'Vho2ATQW']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->updateReturning(['str' => 'M3bJmjWz']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'M3bJmjWz', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['update "example" set "str" = ? returning *'], array_column($queries, 'query'));
    }

    public function testUpdateReturningAllWithTimestamps(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'OWaZuXS8']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateReturning(['str' => 'EOUuTNYm']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'EOUuTNYm', 'created_at' => null, 'updated_at' => now()->getTimestamp(), 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['update "example" set "str" = ?, "updated_at" = ? where "example"."deleted_at" is null returning *'], array_column($queries, 'query'));
    }

    public function testUpdateReturningSelection(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'IKDZd35j']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->updateReturning(['str' => 'VI5pTLsg'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'VI5pTLsg']], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['update "example" set "str" = ? returning "str"'], array_column($queries, 'query'));
    }

    public function testUpdateReturningSelectionwithTimestamps(): void
    {
        with(new Example())->newQuery()->insert(['str' => 'MzD2gVTR']);
        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->updateReturning(['str' => 'BBmUFsI1'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'BBmUFsI1']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['update "example" set "str" = ?, "updated_at" = ? where "example"."deleted_at" is null returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->upsertReturning([['str' => 'WDrZatGx']], ['str'], []);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'WDrZatGx', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning *'], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertAllWithTimestamps(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->upsertReturning([['str' => 'WDrZatGx']], ['str'], []);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'WDrZatGx', 'created_at' => now()->getTimestamp(), 'updated_at' => now()->getTimestamp(), 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("created_at", "str", "updated_at") values (?, ?, ?) on conflict ("str") do update set "updated_at" = "excluded"."updated_at" returning *'], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertSelection(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->upsertReturning([['str' => 'WdSnJCZP']], ['str'], [], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'WdSnJCZP']], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningInsertSelectionWithTimestamps(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->upsertReturning([['str' => 'WdSnJCZP']], ['str'], [], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'WdSnJCZP']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("created_at", "str", "updated_at") values (?, ?, ?) on conflict ("str") do update set "updated_at" = "excluded"."updated_at" returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->upsertReturning([['str' => 'PeKB2qK5']], ['str'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'PeKB2qK5', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict ("str") do update set "str" = "excluded"."str" returning *'], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertAllWithTimestamps(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->upsertReturning([['str' => 'PeKB2qK5']], ['str'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'PeKB2qK5', 'created_at' => now()->getTimestamp(), 'updated_at' => now()->getTimestamp(), 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("created_at", "str", "updated_at") values (?, ?, ?) on conflict ("str") do update set "str" = "excluded"."str", "updated_at" = "excluded"."updated_at" returning *'], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertSelection(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->upsertReturning([['str' => 'MjcznPbN']], ['str'], ['str'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'MjcznPbN']], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict ("str") do update set "str" = "excluded"."str" returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningUpsertSelectionWithTimestamps(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new ExampleTimestamps())
                ->newQuery()
                ->upsertReturning([['str' => 'MjcznPbN']], ['str'], ['str'], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['str' => 'MjcznPbN']], $result->toArray());
            $this->assertInstanceOf(ExampleTimestamps::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("created_at", "str", "updated_at") values (?, ?, ?) on conflict ("str") do update set "str" = "excluded"."str", "updated_at" = "excluded"."updated_at" returning "str"'], array_column($queries, 'query'));
    }

    public function testUpsertReturningWithEmptyValuesAndNullUpdates(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->upsertReturning([], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([], $result->toArray());
        });
        $this->assertEquals([], array_column($queries, 'query'));
    }

    public function testUpsertReturningWithNullUpdates(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = with(new Example())
                ->newQuery()
                ->upsertReturning([['str' => 'PeKB2qK5']], ['str']);

            $this->assertInstanceOf(Collection::class, $result);
            $this->assertEquals([['id' => 1, 'str' => 'PeKB2qK5', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]], $result->toArray());
            $this->assertInstanceOf(Example::class, $result->first());
        });
        $this->assertEquals(['insert into "example" ("str") values (?) on conflict ("str") do update set "str" = "excluded"."str" returning *'], array_column($queries, 'query'));
    }
}

class Example extends Model
{
    public $dateFormat = 'Y-m-d H:i:sO';
    public $guarded = [];
    public $table = 'example';
    public $timestamps = false;
}

class ExampleTimestamps extends Example
{
    use SoftDeletes;
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];
}
