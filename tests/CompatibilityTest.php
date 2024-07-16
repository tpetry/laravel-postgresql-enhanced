<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests;

use DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class CompatibilityTest extends TestCase
{
    // With Laravel 11.15.0 the behaviour of commands within a migration has been changed. The ordering is now important
    // which is a BC behaviour break (https://github.com/laravel/framework/pull/51373). So the implementation has been
    // changed drastically which has lead to issue (https://github.com/tpetry/laravel-postgresql-enhanced/issues/85).
    public function testCompatabilityMigrationOrdering(): void
    {
        DB::statement('create table test()');
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test', static function (Blueprint $table): void {
                $table->text('column_one');
                $table->text('column_two');
            });
        });

        $expected = match (true) {
            version_compare(App::version(), '11.15.0', '>=') => [
                'alter table "test" add column "column_one" text not null',
                'alter table "test" add column "column_two" text not null',
            ],
            default => [
                'alter table "test" add column "column_one" text not null, add column "column_two" text not null',
            ],
        };

        $this->assertEquals($expected, array_column($queries, 'query'));
    }
}
