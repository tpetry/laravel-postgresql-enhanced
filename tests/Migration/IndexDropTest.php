<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Composer\Semver\Comparator;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class IndexDropTest extends TestCase
{
    public function testDropFullTextIfExistsByColumn(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_960082', function (Blueprint $table): void {
            $table->string('col_700752')->fulltext();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_960082', function (Blueprint $table): void {
                $table->dropFulltextIfExists(['col_700752']);
            });
        });
        $this->assertEquals(['drop index if exists "test_960082_col_700752_fulltext"'], array_column($queries, 'query'));
    }

    public function testDropFullTextIfExistsByName(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_458230', function (Blueprint $table): void {
            $table->string('col_719197')->fulltext('index_337012');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_458230', function (Blueprint $table): void {
                $table->dropIndexIfExists('index_337012');
            });
        });
        $this->assertEquals(['drop index if exists "index_337012"'], array_column($queries, 'query'));
    }

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
            $table->string('col_632746');
            $table->primary('col_632746', 'pkey_632746');
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
        $this->app->get('db.connection')->statement('create table "test_663598" ("col_377964" box, "col_211451" box)');
        $this->app->get('db.connection')->statement('create index "test_663598_col_377964_col_211451_spatialindex" on "test_663598" using gist ("col_377964", "col_211451")');
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_663598', function (Blueprint $table): void {
                $table->dropSpatialIndexIfExists(['col_377964', 'col_211451']);
            });
        });
        $this->assertEquals(['drop index if exists "test_663598_col_377964_col_211451_spatialindex"'], array_column($queries, 'query'));
    }

    public function testDropSpatialIndexIfExistsByName(): void
    {
        $this->app->get('db.connection')->statement('create table "test_153372" ("col_470747" box)');
        $this->app->get('db.connection')->statement('create index "index_504502" on "test_153372" using gist ("col_470747")');
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
            $table->string('col_905394')->unique('unique_905394');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_129734', function (Blueprint $table): void {
                $table->dropUniqueIfExists('unique_905394');
            });
        });
        $this->assertEquals(['alter table "test_129734" drop constraint if exists "unique_905394"'], array_column($queries, 'query'));
    }

    public function testDropUniqueIndexByColumn(): void
    {
        Schema::create('test_994632', function (Blueprint $table): void {
            $table->string('col_823350');
            $table->uniqueIndex('col_823350');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_994632', function (Blueprint $table): void {
                $table->dropUniqueIndex(['col_823350']);
            });
        });
        $this->assertEquals(['drop index "test_994632_col_823350_unique"'], array_column($queries, 'query'));
    }

    public function testDropUniqueIndexByName(): void
    {
        Schema::create('test_370499', function (Blueprint $table): void {
            $table->string('col_431653');
            $table->uniqueIndex('col_431653', 'unique_476787');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_370499', function (Blueprint $table): void {
                $table->dropUniqueIndex('unique_476787');
            });
        });
        $this->assertEquals(['drop index "unique_476787"'], array_column($queries, 'query'));
    }

    public function testDropUniqueIndexIfExistsByColumn(): void
    {
        Schema::create('test_426583', function (Blueprint $table): void {
            $table->string('col_555473');
            $table->uniqueIndex('col_555473');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_426583', function (Blueprint $table): void {
                $table->dropUniqueIndexIfExists(['col_555473']);
            });
        });
        $this->assertEquals(['drop index if exists "test_426583_col_555473_unique"'], array_column($queries, 'query'));
    }

    public function testDropUniqueIndexIfExistsByName(): void
    {
        Schema::create('test_849821', function (Blueprint $table): void {
            $table->string('col_320750');
            $table->uniqueIndex('col_320750', 'unique_775368');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_849821', function (Blueprint $table): void {
                $table->dropUniqueIndexIfExists('unique_775368');
            });
        });
        $this->assertEquals(['drop index if exists "unique_775368"'], array_column($queries, 'query'));
    }
}
