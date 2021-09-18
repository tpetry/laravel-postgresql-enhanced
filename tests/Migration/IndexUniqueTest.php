<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class IndexUniqueTest extends TestCase
{
    public function testUniqueIndexByColumn(): void
    {
        Schema::create('test_800299', function (Blueprint $table): void {
            $table->string('col_494598');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_800299', function (Blueprint $table): void {
                $table->uniqueIndex(['col_494598']);
            });
        });
        $this->assertEquals(['create unique index "test_800299_col_494598_unique" on "test_800299" ("col_494598")'], array_column($queries, 'query'));
    }

    public function testUniqueIndexByName(): void
    {
        Schema::create('test_645101', function (Blueprint $table): void {
            $table->string('col_173311');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_645101', function (Blueprint $table): void {
                $table->uniqueIndex(['col_173311'], 'unique_229201');
            });
        });
        $this->assertEquals(['create unique index "unique_229201" on "test_645101" ("col_173311")'], array_column($queries, 'query'));
    }
}
