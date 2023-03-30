<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class TableOptionsTest extends TestCase
{
    public function testUnlogged(): void
    {
        $this->getConnection()->statement('create table test()');
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test', function (Blueprint $table): void {
                $table->unlogged();
                $table->unlogged(true);
                $table->unlogged(false);
            });
        });

        $this->assertEquals([
            'alter table "test" set unlogged',
            'alter table "test" set unlogged',
            'alter table "test" set logged',
        ], array_column($queries, 'query'));
    }
}
