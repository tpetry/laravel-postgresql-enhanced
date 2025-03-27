<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Carbon\Carbon;
use Composer\Semver\Comparator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class BuilderUpsertPartialTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(); // only compatible way of freezing time in Laravel 6
        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL,
                val int NOT NULL,
                created_at timestamptz,
                updated_at timestamptz,
                deleted_at timestamptz
            );
            CREATE UNIQUE INDEX example_partial ON example (str) WHERE deleted_at IS NULL;
        ');
    }

    public function testUpsertPartialWhereQuery(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.10.0')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = (new ExamplePartial())
                ->newQuery()
                ->upsertPartial([['str' => 'JKLkmraa', 'val' => 849351]], ['str'], ['val'], fn (Builder $query) => $query->whereNull('deleted_at'));

            $this->assertEquals(1, $result);
        });
        $this->assertEquals(['insert into "example" ("str", "val") values (?, ?) on conflict ("str") where "deleted_at" is null do update set "val" = "excluded"."val"'], array_column($queries, 'query'));
    }

    public function testUpsertPartialWhereString(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.10.0')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = (new ExamplePartial())
                ->newQuery()
                ->upsertPartial([['str' => 'kIhqPWDC', 'val' => 623169]], ['str'], ['val'], 'deleted_at IS NULL');

            $this->assertEquals(1, $result);
        });
        $this->assertEquals(['insert into "example" ("str", "val") values (?, ?) on conflict ("str") where deleted_at IS NULL do update set "val" = "excluded"."val"'], array_column($queries, 'query'));
    }
}

class ExamplePartial extends Model
{
    public $dateFormat = 'Y-m-d H:i:sO';
    public $guarded = [];
    public $table = 'example';
    public $timestamps = false;
}
