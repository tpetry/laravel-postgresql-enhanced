<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class TableOptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->statement('create table test()');
    }

    public function testStorageParameters(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test', function (Blueprint $table): void {
                $table->with([
                    'autovacuum_analyze_scale_factor' => 0.02,
                    'fillfactor' => 90,
                ]);
            });
        });

        $this->assertEquals([
            'alter table "test" set (autovacuum_analyze_scale_factor = 0.02, fillfactor = 90)',
        ], array_column($queries, 'query'));
    }

    public function testUnlogged(): void
    {
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
