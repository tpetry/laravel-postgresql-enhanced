<?php

declare(strict_types=1);

namespace Query;

use Composer\Semver\Comparator;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class UpsertPartialTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                col int NOT NULL,
                val int NOT NULL,
                deleted_at timestamptz
            );
            CREATE UNIQUE INDEX example_partial ON example (col) WHERE deleted_at IS NULL;
        ');
    }

    public function testUpsertPartialWhereQuery(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.10.0')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertPartial([['col' => 446737, 'val' => 896013], ['col' => 719244, 'val' => 572449]], ['col'], ['val'], fn (Builder $query) => $query->whereNull('deleted_at'));

            $this->assertEquals(2, $result);
        });

        $this->assertEquals([
            'insert into "example" ("col", "val") values (?, ?), (?, ?) on conflict ("col") where "deleted_at" is null do update set "val" = "excluded"."val"',
        ], array_column($queries, 'query'));
    }

    public function testUpsertPartialWhereString(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.10.0')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $queries = $this->withQueryLog(function (): void {
            $result = $this->getConnection()
                ->table('example')
                ->upsertPartial([['col' => 278449, 'val' => 733801], ['col' => 335775, 'val' => 120552]], ['col'], ['val'], 'deleted_at IS NULL');

            $this->assertEquals(2, $result);
        });

        $this->assertEquals([
            'insert into "example" ("col", "val") values (?, ?), (?, ?) on conflict ("col") where deleted_at IS NULL do update set "val" = "excluded"."val"',
        ], array_column($queries, 'query'));
    }
}
