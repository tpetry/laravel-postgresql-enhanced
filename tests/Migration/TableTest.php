<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class TableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->statement('create table test()');
    }

    public function testAddColumnInitial(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test', function (Blueprint $table): void {
                $table->text('col_829351')->initial('val_656165');
            });
        });

        $this->assertEquals([
            'alter table "test" add column "col_829351" text not null default \'val_656165\'',
            'alter table "test" alter column "col_829351" drop default',
        ], array_column($queries, 'query'));
    }

    public function testAddColumnInitialWithDefault(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test', function (Blueprint $table): void {
                $table->text('col_437065')->initial('val_217786')->default('val_121964');
            });
        });

        $this->assertEquals([
            'alter table "test" add column "col_437065" text not null default \'val_217786\'',
            'alter table "test" alter column "col_437065" set default \'val_121964\'',
        ], array_column($queries, 'query'));
    }
}
