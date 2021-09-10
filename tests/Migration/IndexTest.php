<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Database\Query\Builder;
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

    public function testDropPartialUniqueByColumn(): void
    {
        Schema::create('test_944685', function (Blueprint $table): void {
            $table->string('col_503208');
            $table->partialUnique('col_503208', fn (Builder $query) => $query->whereNotNull('col_503208'));
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_944685', function (Blueprint $table): void {
                $table->dropPartialUnique(['col_503208']);
            });
        });
        $this->assertEquals(['drop index "test_944685_col_503208_unique"'], array_column($queries, 'query'));
    }

    public function testDropPartialUniqueByName(): void
    {
        Schema::create('test_873708', function (Blueprint $table): void {
            $table->string('col_231939');
            $table->partialUnique('col_231939', fn (Builder $query) => $query->whereNotNull('col_231939'), 'unique_231939');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_873708', function (Blueprint $table): void {
                $table->dropPartialUnique('unique_231939');
            });
        });
        $this->assertEquals(['drop index "unique_231939"'], array_column($queries, 'query'));
    }

    public function testDropPartialUniqueIfExistsByColumn(): void
    {
        Schema::create('test_638567', function (Blueprint $table): void {
            $table->string('col_173938');
            $table->partialUnique('col_173938', fn (Builder $query) => $query->whereNotNull('col_173938'));
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_638567', function (Blueprint $table): void {
                $table->dropPartialUniqueIfExists(['col_173938']);
            });
        });
        $this->assertEquals(['drop index if exists "test_638567_col_173938_unique"'], array_column($queries, 'query'));
    }

    public function testDropPartialUniqueIfExistsByName(): void
    {
        Schema::create('test_825040', function (Blueprint $table): void {
            $table->string('col_365888');
            $table->partialUnique('col_365888', fn (Builder $query) => $query->whereNotNull('col_365888'), 'unique_365888');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_825040', function (Blueprint $table): void {
                $table->dropPartialUniqueIfExists('unique_365888');
            });
        });
        $this->assertEquals(['drop index if exists "unique_365888"'], array_column($queries, 'query'));
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

    public function testPartialIndexByColumn(): void
    {
        Schema::create('test_718079', function (Blueprint $table): void {
            $table->string('col_191384');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_718079', function (Blueprint $table): void {
                $table->partialIndex(['col_191384'], fn (Builder $query) => $query->whereNotNull('col_191384'));
            });
        });
        $this->assertEquals(['create index "test_718079_col_191384_index" on "test_718079" ("col_191384") where "col_191384" is not null'], array_column($queries, 'query'));
    }

    public function testPartialIndexByName(): void
    {
        Schema::create('test_563532', function (Blueprint $table): void {
            $table->string('col_563532');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_563532', function (Blueprint $table): void {
                $table->partialIndex(['col_563532'], fn (Builder $query) => $query->whereNotNull('col_563532'), 'partial_727161');
            });
        });
        $this->assertEquals(['create index "partial_727161" on "test_563532" ("col_563532") where "col_563532" is not null'], array_column($queries, 'query'));
    }

    public function testPartialSpatialIndexByColumn(): void
    {
        $this->app->get('db.connection')->statement('create table "test_187489" ("col_527332" box)');
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_187489', function (Blueprint $table): void {
                $table->partialSpatialIndex(['col_527332'], fn (Builder $query) => $query->whereNotNull('col_527332'));
            });
        });
        $this->assertEquals(['create index "test_187489_col_527332_spatialindex" on "test_187489" using gist ("col_527332") where "col_527332" is not null'], array_column($queries, 'query'));
    }

    public function testPartialSpatialIndexByName(): void
    {
        $this->app->get('db.connection')->statement('create table "test_415147" ("col_161022" box)');
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_415147', function (Blueprint $table): void {
                $table->partialSpatialIndex(['col_161022'], fn (Builder $query) => $query->whereNotNull('col_161022'), 'partial_357565');
            });
        });
        $this->assertEquals(['create index "partial_357565" on "test_415147" using gist ("col_161022") where "col_161022" is not null'], array_column($queries, 'query'));
    }

    public function testPartialUniqueByColumn(): void
    {
        Schema::create('test_614037', function (Blueprint $table): void {
            $table->string('col_403192');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_614037', function (Blueprint $table): void {
                $table->partialUnique(['col_403192'], fn (Builder $query) => $query->whereNotNull('col_403192'));
            });
        });
        $this->assertEquals(['create unique index "test_614037_col_403192_unique" on "test_614037" ("col_403192") where "col_403192" is not null'], array_column($queries, 'query'));
    }

    public function testPartialUniqueByName(): void
    {
        Schema::create('test_578729', function (Blueprint $table): void {
            $table->string('col_818344');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_578729', function (Blueprint $table): void {
                $table->partialUnique(['col_818344'], fn (Builder $query) => $query->whereNotNull('col_818344'), 'partial_522558');
            });
        });
        $this->assertEquals(['create unique index "partial_522558" on "test_578729" ("col_818344") where "col_818344" is not null'], array_column($queries, 'query'));
    }

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
