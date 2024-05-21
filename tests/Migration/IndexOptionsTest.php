<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Composer\Semver\Comparator;
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class IndexOptionsTest extends TestCase
{
    public function testIfNotExistsFulltextByColumn(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_806712', function (Blueprint $table): void {
            $table->string('col_274742');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_806712', function (Blueprint $table): void {
                $table->fullText(['col_274742'])->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "test_806712_col_274742_fulltext" on "test_806712" using gin ((to_tsvector(\'english\', "col_274742")))'], array_column($queries, 'query'));
    }

    public function testIfNotExistsFulltextByName(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_940928', function (Blueprint $table): void {
            $table->string('col_563370');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_940928', function (Blueprint $table): void {
                $table->fullText(['col_563370'], 'index_726055')->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "index_726055" on "test_940928" using gin ((to_tsvector(\'english\', "col_563370")))'], array_column($queries, 'query'));
    }

    public function testIfNotExistsIndexByColumn(): void
    {
        Schema::create('test_233704', function (Blueprint $table): void {
            $table->string('col_484213');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_233704', function (Blueprint $table): void {
                $table->index(['col_484213'])->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "test_233704_col_484213_index" on "test_233704" ("col_484213")'], array_column($queries, 'query'));
    }

    public function testIfNotExistsIndexByName(): void
    {
        Schema::create('test_101004', function (Blueprint $table): void {
            $table->string('col_749919');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_101004', function (Blueprint $table): void {
                $table->index(['col_749919'], 'index_605253')->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "index_605253" on "test_101004" ("col_749919")'], array_column($queries, 'query'));
    }

    public function testIfNotExistsRawIndex(): void
    {
        if (Comparator::lessThan($this->app->version(), '7.7.0')) {
            $this->markTestSkipped('Raw indexes have been added in a later Laravel version.');
        }

        Schema::create('test_302103', function (Blueprint $table): void {
            $table->string('col_650398');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_302103', function (Blueprint $table): void {
                $table->rawIndex('col_650398', 'idx_728687')->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "idx_728687" on "test_302103" (col_650398)'], array_column($queries, 'query'));
    }

    public function testIfNotExistsSpatialIndexByColumn(): void
    {
        Schema::create('test_508190', function (Blueprint $table): void {
            $table->integerRange('col_402778');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_508190', function (Blueprint $table): void {
                $table->spatialIndex(['col_402778'])->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "test_508190_col_402778_spatialindex" on "test_508190" using gist ("col_402778")'], array_column($queries, 'query'));
    }

    public function testIfNotExistsSpatialIndexByName(): void
    {
        Schema::create('test_521621', function (Blueprint $table): void {
            $table->integerRange('col_990392');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_521621', function (Blueprint $table): void {
                $table->spatialIndex(['col_990392'], 'index_124848')->ifNotExists();
            });
        });
        $this->assertEquals(['create index if not exists "index_124848" on "test_521621" using gist ("col_990392")'], array_column($queries, 'query'));
    }

    public function testIfNotExistsUniqueIndexByColumn(): void
    {
        Schema::create('test_868599', function (Blueprint $table): void {
            $table->string('col_571721');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_868599', function (Blueprint $table): void {
                $table->uniqueIndex(['col_571721'])->ifNotExists();
            });
        });
        $this->assertEquals(['create unique index if not exists "test_868599_col_571721_unique" on "test_868599" ("col_571721")'], array_column($queries, 'query'));
    }

    public function testIfNotExistsUniqueIndexByName(): void
    {
        Schema::create('test_324400', function (Blueprint $table): void {
            $table->string('col_208151');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_324400', function (Blueprint $table): void {
                $table->uniqueIndex(['col_208151'], 'index_810276')->ifNotExists();
            });
        });
        $this->assertEquals(['create unique index if not exists "index_810276" on "test_324400" ("col_208151")'], array_column($queries, 'query'));
    }

    public function testIncludeIndexByColumn(): void
    {
        Schema::create('test_130163', function (Blueprint $table): void {
            $table->string('col_133517');
            $table->string('col_838026');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_130163', function (Blueprint $table): void {
                $table->index(['col_133517'])->include(['col_838026']);
            });
        });
        $this->assertEquals(['create index "test_130163_col_133517_index" on "test_130163" ("col_133517") include ("col_838026")'], array_column($queries, 'query'));
    }

    public function testIncludeIndexByName(): void
    {
        Schema::create('test_943012', function (Blueprint $table): void {
            $table->string('col_575492');
            $table->string('col_699784');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_943012', function (Blueprint $table): void {
                $table->index(['col_575492'], 'index_326954')->include('col_699784');
            });
        });
        $this->assertEquals(['create index "index_326954" on "test_943012" ("col_575492") include ("col_699784")'], array_column($queries, 'query'));
    }

    public function testIncludeRawIndex(): void
    {
        if (Comparator::lessThan($this->app->version(), '7.7.0')) {
            $this->markTestSkipped('Raw indexes have been added in a later Laravel version.');
        }

        Schema::create('test_550509', function (Blueprint $table): void {
            $table->string('col_197581');
            $table->string('col_535872');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_550509', function (Blueprint $table): void {
                $table->rawIndex('col_197581', 'idx_253616')->include(['col_535872']);
            });
        });
        $this->assertEquals(['create index "idx_253616" on "test_550509" (col_197581) include ("col_535872")'], array_column($queries, 'query'));
    }

    public function testIncludeSpatialIndexByColumn(): void
    {
        Schema::create('test_780591', function (Blueprint $table): void {
            $table->integerRange('col_450233');
            $table->string('col_570386');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_780591', function (Blueprint $table): void {
                $table->spatialIndex(['col_450233'])->include(['col_570386']);
            });
        });
        $this->assertEquals(['create index "test_780591_col_450233_spatialindex" on "test_780591" using gist ("col_450233") include ("col_570386")'], array_column($queries, 'query'));
    }

    public function testIncludeSpatialIndexByName(): void
    {
        Schema::create('test_900795', function (Blueprint $table): void {
            $table->integerRange('col_668927');
            $table->string('col_381249');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_900795', function (Blueprint $table): void {
                $table->spatialIndex(['col_668927'], 'index_444499')->include('col_381249');
            });
        });
        $this->assertEquals(['create index "index_444499" on "test_900795" using gist ("col_668927") include ("col_381249")'], array_column($queries, 'query'));
    }

    public function testIncludeUniqueIndexByColumn(): void
    {
        Schema::create('test_263710', function (Blueprint $table): void {
            $table->string('col_865593');
            $table->string('col_972446');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_263710', function (Blueprint $table): void {
                $table->uniqueIndex(['col_865593'])->include(['col_972446']);
            });
        });
        $this->assertEquals(['create unique index "test_263710_col_865593_unique" on "test_263710" ("col_865593") include ("col_972446")'], array_column($queries, 'query'));
    }

    public function testIncludeUniqueIndexByName(): void
    {
        Schema::create('test_114041', function (Blueprint $table): void {
            $table->string('col_878088');
            $table->string('col_958638');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_114041', function (Blueprint $table): void {
                $table->uniqueIndex(['col_878088'], 'index_151431')->include('col_958638');
            });
        });
        $this->assertEquals(['create unique index "index_151431" on "test_114041" ("col_878088") include ("col_958638")'], array_column($queries, 'query'));
    }

    public function testLanguageFulltextByColumn(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_805943', function (Blueprint $table): void {
            $table->string('col_721591');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_805943', function (Blueprint $table): void {
                $table->fullText(['col_721591'])->language('simple');
            });
        });
        $this->assertEquals(['create index "test_805943_col_721591_fulltext" on "test_805943" using gin ((to_tsvector(\'simple\', "col_721591")))'], array_column($queries, 'query'));
    }

    public function testLanguageFulltextByName(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_575245', function (Blueprint $table): void {
            $table->string('col_400891');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_575245', function (Blueprint $table): void {
                $table->fullText(['col_400891'], 'index_618564')->language('simple');
            });
        });
        $this->assertEquals(['create index "index_618564" on "test_575245" using gin ((to_tsvector(\'simple\', "col_400891")))'], array_column($queries, 'query'));
    }

    public function testNullsNotDistinctRawIndex(): void
    {
        if (Comparator::lessThan($this->app->version(), '7.7.0')) {
            $this->markTestSkipped('Raw indexes have been added in a later Laravel version.');
        }

        if (Comparator::lessThan($this->getConnection()->serverVersion(), '15')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 15.');
        }

        Schema::create('test_235072', function (Blueprint $table): void {
            $table->string('col_864636')->nullable();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_235072', function (Blueprint $table): void {
                $table->rawIndex('col_864636', 'idx_400631')->nullsNotDistinct();
            });
        });
        $this->assertEquals(['create index "idx_400631" on "test_235072" (col_864636) nulls not distinct'], array_column($queries, 'query'));
    }

    public function testNullsNotDistinctUniqueIndexByColumn(): void
    {
        if (Comparator::lessThan($this->getConnection()->serverVersion(), '15')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 15.');
        }

        Schema::create('test_362300', function (Blueprint $table): void {
            $table->string('col_175058')->nullable();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_362300', function (Blueprint $table): void {
                $table->uniqueIndex(['col_175058'])->nullsNotDistinct();
            });
        });
        $this->assertEquals(['create unique index "test_362300_col_175058_unique" on "test_362300" ("col_175058") nulls not distinct'], array_column($queries, 'query'));
    }

    public function testNullsNotDistinctUniqueIndexByName(): void
    {
        if (Comparator::lessThan($this->getConnection()->serverVersion(), '15')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 15.');
        }

        Schema::create('test_269321', function (Blueprint $table): void {
            $table->string('col_729148')->nullable();
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_269321', function (Blueprint $table): void {
                $table->uniqueIndex(['col_729148'], 'index_992648')->nullsNotDistinct();
            });
        });
        $this->assertEquals(['create unique index "index_992648" on "test_269321" ("col_729148") nulls not distinct'], array_column($queries, 'query'));
    }

    public function testPartialFulltextByColumn(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_311536', function (Blueprint $table): void {
            $table->string('col_877250');
            $table->string('col_599221');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_311536', function (Blueprint $table): void {
                $table->fullText(['col_877250'])->where(fn (Builder $query) => $query->whereNull('col_599221'));
            });
        });
        $this->assertEquals(['create index "test_311536_col_877250_fulltext" on "test_311536" using gin ((to_tsvector(\'english\', "col_877250"))) where "col_599221" is null'], array_column($queries, 'query'));
    }

    public function testPartialFulltextByName(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_606168', function (Blueprint $table): void {
            $table->string('col_229595');
            $table->string('col_711664');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_606168', function (Blueprint $table): void {
                $table->fullText(['col_229595'], 'index_461578')->where('col_711664 is null');
            });
        });
        $this->assertEquals(['create index "index_461578" on "test_606168" using gin ((to_tsvector(\'english\', "col_229595"))) where col_711664 is null'], array_column($queries, 'query'));
    }

    public function testPartialIndexByColumn(): void
    {
        Schema::create('test_723016', function (Blueprint $table): void {
            $table->string('col_437040');
            $table->string('col_922611');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_723016', function (Blueprint $table): void {
                $table->index(['col_437040'])->where(fn (Builder $query) => $query->whereNull('col_922611'));
            });
        });
        $this->assertEquals(['create index "test_723016_col_437040_index" on "test_723016" ("col_437040") where "col_922611" is null'], array_column($queries, 'query'));
    }

    public function testPartialIndexByName(): void
    {
        Schema::create('test_551914', function (Blueprint $table): void {
            $table->string('col_306144');
            $table->string('col_203409');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_551914', function (Blueprint $table): void {
                $table->index(['col_306144'], 'index_402517')->where('col_203409 is null');
            });
        });
        $this->assertEquals(['create index "index_402517" on "test_551914" ("col_306144") where col_203409 is null'], array_column($queries, 'query'));
    }

    public function testPartialRawIndex(): void
    {
        if (Comparator::lessThan($this->app->version(), '7.7.0')) {
            $this->markTestSkipped('Raw indexes have been added in a later Laravel version.');
        }

        Schema::create('test_992618', function (Blueprint $table): void {
            $table->string('col_306901');
            $table->integer('col_554001');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_992618', function (Blueprint $table): void {
                $table->rawIndex('col_306901', 'idx_836465')->where(fn (Builder $query) => $query->where('col_554001', 611288));
            });
        });
        $this->assertEquals(['create index "idx_836465" on "test_992618" (col_306901) where "col_554001" = 611288'], array_column($queries, 'query'));
    }

    public function testPartialSpatialIndexByColumn(): void
    {
        Schema::create('test_726064', function (Blueprint $table): void {
            $table->integerRange('col_503874');
            $table->integer('col_223582');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_726064', function (Blueprint $table): void {
                $table->spatialIndex(['col_503874'])->where(fn (Builder $query) => $query->whereIn('col_223582', [413742]));
            });
        });
        $this->assertEquals(['create index "test_726064_col_503874_spatialindex" on "test_726064" using gist ("col_503874") where "col_223582" in (413742)'], array_column($queries, 'query'));
    }

    public function testPartialSpatialIndexByName(): void
    {
        Schema::create('test_181740', function (Blueprint $table): void {
            $table->integerRange('col_285795');
            $table->integer('col_575425');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_181740', function (Blueprint $table): void {
                $table->spatialIndex(['col_285795'], 'index_812661')->where('col_575425 in(674016)');
            });
        });
        $this->assertEquals(['create index "index_812661" on "test_181740" using gist ("col_285795") where col_575425 in(674016)'], array_column($queries, 'query'));
    }

    public function testPartialUniqueIndexByColumn(): void
    {
        Schema::create('test_262832', function (Blueprint $table): void {
            $table->string('col_656454');
            $table->integer('col_476983');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_262832', function (Blueprint $table): void {
                $table->uniqueIndex(['col_656454'])->where(fn (Builder $query) => $query->where('col_476983', 966923));
            });
        });
        $this->assertEquals(['create unique index "test_262832_col_656454_unique" on "test_262832" ("col_656454") where "col_476983" = 966923'], array_column($queries, 'query'));
    }

    public function testPartialUniqueIndexByName(): void
    {
        Schema::create('test_614344', function (Blueprint $table): void {
            $table->string('col_327595');
            $table->integer('col_127923');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_614344', function (Blueprint $table): void {
                $table->uniqueIndex(['col_327595'], 'index_729619')->where('col_127923 = 840879');
            });
        });
        $this->assertEquals(['create unique index "index_729619" on "test_614344" ("col_327595") where col_127923 = 840879'], array_column($queries, 'query'));
    }

    public function testWeightFulltextByColumn(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_517379', function (Blueprint $table): void {
            $table->string('col_383934');
            $table->string('col_996362');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_517379', function (Blueprint $table): void {
                $table->fullText(['col_383934', 'col_996362'])->weight(['A', 'B']);
            });
        });
        $this->assertEquals(['create index "test_517379_col_383934_col_996362_fulltext" on "test_517379" using gin ((setweight(to_tsvector(\'english\', "col_383934"), \'A\') || setweight(to_tsvector(\'english\', "col_996362"), \'B\')))'], array_column($queries, 'query'));
    }

    public function testWeightFulltextByName(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_460239', function (Blueprint $table): void {
            $table->string('col_705289');
            $table->string('col_996385');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_460239', function (Blueprint $table): void {
                $table->fullText(['col_705289', 'col_996385'], 'index_183210')->weight(['A', 'B']);
            });
        });
        $this->assertEquals(['create index "index_183210" on "test_460239" using gin ((setweight(to_tsvector(\'english\', "col_705289"), \'A\') || setweight(to_tsvector(\'english\', "col_996385"), \'B\')))'], array_column($queries, 'query'));
    }

    public function testWithFulltextByColumn(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_136102', function (Blueprint $table): void {
            $table->string('col_856942');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_136102', function (Blueprint $table): void {
                $table->fullText(['col_856942'])->with(['fastupdate' => 'off']);
            });
        });
        $this->assertEquals(['create index "test_136102_col_856942_fulltext" on "test_136102" using gin ((to_tsvector(\'english\', "col_856942"))) with (fastupdate = off)'], array_column($queries, 'query'));
    }

    public function testWithFulltextByName(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.74.0')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        Schema::create('test_251686', function (Blueprint $table): void {
            $table->string('col_307576');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_251686', function (Blueprint $table): void {
                $table->fullText(['col_307576'], 'index_767340')->with(['fastupdate' => 'off']);
            });
        });
        $this->assertEquals(['create index "index_767340" on "test_251686" using gin ((to_tsvector(\'english\', "col_307576"))) with (fastupdate = off)'], array_column($queries, 'query'));
    }

    public function testWithIndexByColumn(): void
    {
        Schema::create('test_679473', function (Blueprint $table): void {
            $table->string('col_534422');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_679473', function (Blueprint $table): void {
                $table->index(['col_534422'])->with(['fillfactor' => 80]);
            });
        });
        $this->assertEquals(['create index "test_679473_col_534422_index" on "test_679473" ("col_534422") with (fillfactor = 80)'], array_column($queries, 'query'));
    }

    public function testWithIndexByName(): void
    {
        Schema::create('test_533609', function (Blueprint $table): void {
            $table->string('col_889546');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_533609', function (Blueprint $table): void {
                $table->index(['col_889546'], 'index_477176')->with(['fillfactor' => 80]);
            });
        });
        $this->assertEquals(['create index "index_477176" on "test_533609" ("col_889546") with (fillfactor = 80)'], array_column($queries, 'query'));
    }

    public function testWithRawIndex(): void
    {
        if (Comparator::lessThan($this->app->version(), '7.7.0')) {
            $this->markTestSkipped('Raw indexes have been added in a later Laravel version.');
        }

        Schema::create('test_136121', function (Blueprint $table): void {
            $table->string('col_933646');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_136121', function (Blueprint $table): void {
                $table->rawIndex('col_933646', 'idx_547689')->with(['deduplicate_items' => true]);
            });
        });
        $this->assertEquals(['create index "idx_547689" on "test_136121" (col_933646) with (deduplicate_items = on)'], array_column($queries, 'query'));
    }

    public function testWithSpatialIndexByColumn(): void
    {
        Schema::create('test_837999', function (Blueprint $table): void {
            $table->integerRange('col_705526');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_837999', function (Blueprint $table): void {
                $table->spatialIndex(['col_705526'])->with(['buffering' => 'auto']);
            });
        });
        $this->assertEquals(['create index "test_837999_col_705526_spatialindex" on "test_837999" using gist ("col_705526") with (buffering = auto)'], array_column($queries, 'query'));
    }

    public function testWithSpatialIndexByName(): void
    {
        Schema::create('test_183851', function (Blueprint $table): void {
            $table->integerRange('col_297604');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_183851', function (Blueprint $table): void {
                $table->spatialIndex(['col_297604'], 'index_675551')->with(['buffering' => 'auto']);
            });
        });
        $this->assertEquals(['create index "index_675551" on "test_183851" using gist ("col_297604") with (buffering = auto)'], array_column($queries, 'query'));
    }

    public function testWithUniqueIndexByColumn(): void
    {
        Schema::create('test_457083', function (Blueprint $table): void {
            $table->string('col_190610');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_457083', function (Blueprint $table): void {
                $table->uniqueIndex(['col_190610'])->with(['deduplicate_items' => true]);
            });
        });
        $this->assertEquals(['create unique index "test_457083_col_190610_unique" on "test_457083" ("col_190610") with (deduplicate_items = on)'], array_column($queries, 'query'));
    }

    public function testWithUniqueIndexByName(): void
    {
        Schema::create('test_271032', function (Blueprint $table): void {
            $table->string('col_770102');
        });
        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_271032', function (Blueprint $table): void {
                $table->uniqueIndex(['col_770102'], 'index_655511')->with(['deduplicate_items' => true]);
            });
        });
        $this->assertEquals(['create unique index "index_655511" on "test_271032" ("col_770102") with (deduplicate_items = on)'], array_column($queries, 'query'));
    }
}
