<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class IndexColumnsTest extends TestCase
{
    public function testIndexEscapedColumn(): void
    {
        Schema::create('test_308394', function (Blueprint $table): void {
            $table->string('col_797129');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_308394', function (Blueprint $table): void {
                $table->index(['"col_797129"']);
            });
        });
        $this->assertEquals(['create index "test_308394_col_797129_index" on "test_308394" ("col_797129")'], array_column($queries, 'query'));
    }

    public function testIndexFunctionalExpression(): void
    {
        Schema::create('test_831289', function (Blueprint $table): void {
            $table->string('col_475002');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_831289', function (Blueprint $table): void {
                $table->index(['(LOWER(col_475002))'], 'test_831289_func992979');
            });
        });
        $this->assertEquals(['create index "test_831289_func992979" on "test_831289" ((LOWER(col_475002)))'], array_column($queries, 'query'));
    }

    public function testIndexParametrizedColumn(): void
    {
        Schema::create('test_217958', function (Blueprint $table): void {
            $table->string('col_613233');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_217958', function (Blueprint $table): void {
                $table->index(['col_613233 ASC']);
            });
        });
        $this->assertEquals(['create index "test_217958_col_613233_index" on "test_217958" ("col_613233" ASC)'], array_column($queries, 'query'));
    }

    public function testIndexPlainColumn(): void
    {
        Schema::create('test_185445', function (Blueprint $table): void {
            $table->string('col_803421');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_185445', function (Blueprint $table): void {
                $table->index(['col_803421']);
            });
        });
        $this->assertEquals(['create index "test_185445_col_803421_index" on "test_185445" ("col_803421")'], array_column($queries, 'query'));
    }

    public function testSpatialIndexEscapedColumn(): void
    {
        Schema::create('test_703492', function (Blueprint $table): void {
            $table->integerRange('col_935342');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_703492', function (Blueprint $table): void {
                $table->spatialIndex(['"col_935342"']);
            });
        });
        $this->assertEquals(['create index "test_703492_col_935342_spatialindex" on "test_703492" using gist ("col_935342")'], array_column($queries, 'query'));
    }

    public function testSpatialIndexFunctionalExpression(): void
    {
        Schema::create('test_135019', function (Blueprint $table): void {
            $table->ipAddress('col_206720');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_135019', function (Blueprint $table): void {
                $table->spatialIndex(['(netmask(col_206720)) inet_ops'], 'test_135019_func849203');
            });
        });
        $this->assertEquals(['create index "test_135019_func849203" on "test_135019" using gist ((netmask(col_206720)) inet_ops)'], array_column($queries, 'query'));
    }

    public function testSpatialIndexParametrizedColumn(): void
    {
        Schema::create('test_259840', function (Blueprint $table): void {
            $table->integerRange('col_454276');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_259840', function (Blueprint $table): void {
                $table->spatialIndex(['col_454276 range_ops']);
            });
        });
        $this->assertEquals(['create index "test_259840_col_454276_spatialindex" on "test_259840" using gist ("col_454276" range_ops)'], array_column($queries, 'query'));
    }

    public function testSpatialIndexPlainColumn(): void
    {
        Schema::create('test_875006', function (Blueprint $table): void {
            $table->integerRange('col_568824');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_875006', function (Blueprint $table): void {
                $table->spatialIndex(['col_568824']);
            });
        });
        $this->assertEquals(['create index "test_875006_col_568824_spatialindex" on "test_875006" using gist ("col_568824")'], array_column($queries, 'query'));
    }

    public function testUniqueIndexEscapedColumn(): void
    {
        Schema::create('test_976351', function (Blueprint $table): void {
            $table->string('col_561202');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_976351', function (Blueprint $table): void {
                $table->uniqueIndex(['"col_561202"']);
            });
        });
        $this->assertEquals(['create unique index "test_976351_col_561202_unique" on "test_976351" ("col_561202")'], array_column($queries, 'query'));
    }

    public function testUniqueIndexFunctionalExpression(): void
    {
        Schema::create('test_331602', function (Blueprint $table): void {
            $table->string('col_929728');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_331602', function (Blueprint $table): void {
                $table->uniqueIndex(['(LOWER(col_929728))'], 'test_331602_func714604');
            });
        });
        $this->assertEquals(['create unique index "test_331602_func714604" on "test_331602" ((LOWER(col_929728)))'], array_column($queries, 'query'));
    }

    public function testUniqueIndexParametrizedColumn(): void
    {
        Schema::create('test_477236', function (Blueprint $table): void {
            $table->string('col_177070');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_477236', function (Blueprint $table): void {
                $table->uniqueIndex(['col_177070 NULLS FIRST']);
            });
        });
        $this->assertEquals(['create unique index "test_477236_col_177070_unique" on "test_477236" ("col_177070" NULLS FIRST)'], array_column($queries, 'query'));
    }

    public function testUniqueIndexPlainColumn(): void
    {
        Schema::create('test_537068', function (Blueprint $table): void {
            $table->string('col_496793');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_537068', function (Blueprint $table): void {
                $table->uniqueIndex(['col_496793']);
            });
        });
        $this->assertEquals(['create unique index "test_537068_col_496793_unique" on "test_537068" ("col_496793")'], array_column($queries, 'query'));
    }
}
