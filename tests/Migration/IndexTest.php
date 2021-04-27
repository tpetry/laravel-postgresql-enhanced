<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class IndexTest extends TestCase
{
    public function testDropIndexIfExistsByColumn(): void
    {
        Schema::create('test_282503', function (Blueprint $table): void {
            $table->string('col_125474')->index();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_282503', function (Blueprint $table): void {
                $table->dropIndexIfExists(['col_125474']);
            });
        });
        $this->assertEquals(['drop index if exists "test_282503_col_125474_index"'], array_column($queries, 'query'));
    }

    public function testDropIndexIfExistsByName(): void
    {
        Schema::create('test_855776', function (Blueprint $table): void {
            $table->string('col_661848')->index('index_661848');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_855776', function (Blueprint $table): void {
                $table->dropIndexIfExists('index_661848');
            });
        });
        $this->assertEquals(['drop index if exists "index_661848"'], array_column($queries, 'query'));
    }

    public function testDropPrimaryIfExistsByColumn(): void
    {
        Schema::create('test_175007', function (Blueprint $table): void {
            $table->string('col_585036')->primary();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_175007', function (Blueprint $table): void {
                $table->dropPrimaryIfExists(['col_585036']);
            });
        });
        $this->assertEquals(['alter table "test_175007" drop constraint if exists "test_175007_pkey"'], array_column($queries, 'query'));
    }

    public function testDropPrimaryIfExistsByName(): void
    {
        // Note: Laravel ignores name of primary key, the autonamed key is used all the time
        Schema::create('test_152155', function (Blueprint $table): void {
            $table->string('col_632746')->primary('pkey_632746');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_152155', function (Blueprint $table): void {
                $table->dropPrimaryIfExists('pkey_632746');
            });
        });
        $this->assertEquals(['alter table "test_152155" drop constraint if exists "test_152155_pkey"'], array_column($queries, 'query'));
    }

    public function testDropSpatialIndexIfExistsByColumn(): void
    {
        // TODO create real table to test delete command (github postgres container currently has no postgis extension)
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_663598', function (Blueprint $table): void {
                $table->dropSpatialIndexIfExists(['col_377964', 'col_211451']);
            });
        });
        $this->assertEquals(['drop index if exists "test_663598_col_377964_col_211451_spatialindex"'], array_column($queries, 'query'));
    }

    public function testDropSpatialIndexIfExistsByName(): void
    {
        // TODO create real table to test delete command (github postgres container currently has no postgis extension)
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_153372', function (Blueprint $table): void {
                $table->dropSpatialIndexIfExists('index_504502');
            });
        });
        $this->assertEquals(['drop index if exists "index_504502"'], array_column($queries, 'query'));
    }

    public function testDropUniqueIfExistsByColumn(): void
    {
        Schema::create('test_460872', function (Blueprint $table): void {
            $table->string('col_542073')->unique();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_460872', function (Blueprint $table): void {
                $table->dropUniqueIfExists(['col_542073']);
            });
        });
        $this->assertEquals(['alter table "test_460872" drop constraint if exists "test_460872_col_542073_unique"'], array_column($queries, 'query'));
    }

    public function testDropUniqueIfExistsByName(): void
    {
        Schema::create('test_129734', function (Blueprint $table): void {
            $table->string('col_905394')->unique('spatial_905394');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_129734', function (Blueprint $table): void {
                $table->dropUniqueIfExists('spatial_905394');
            });
        });
        $this->assertEquals(['alter table "test_129734" drop constraint if exists "spatial_905394"'], array_column($queries, 'query'));
    }
}
