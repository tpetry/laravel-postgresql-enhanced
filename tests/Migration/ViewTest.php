<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ViewTest extends TestCase
{
    public function testCreateMaterializedViewWithData(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createMaterializedView('test_553409', DB::query()->selectRaw('random() as column_221817'));
        });
        $this->assertEquals(['create materialized view "test_553409" as select random() as column_221817 with data'], array_column($queries, 'query'));
    }

    public function testCreateMaterializedViewWithoutData(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createMaterializedView('test_899394', DB::query()->selectRaw('random() as column_116985'), withData: false);
        });
        $this->assertEquals(['create materialized view "test_899394" as select random() as column_116985 with no data'], array_column($queries, 'query'));
    }

    public function testCreateRecursiveView(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createRecursiveView('test_787482', DB::query()->selectRaw('random() as column_253761'), ['column_253761']);
        });
        $this->assertEquals(['create recursive view "test_787482" ("column_253761") as select random() as column_253761'], array_column($queries, 'query'));
    }

    public function testCreateRecursiveViewOrReplace(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createRecursiveViewOrReplace('test_552475', DB::query()->selectRaw('random() as column_395089'), ['column_395089']);
        });
        $this->assertEquals(['create or replace recursive view "test_552475" ("column_395089") as select random() as column_395089'], array_column($queries, 'query'));
    }

    public function testCreateView(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createView('test_787482', DB::query()->selectRaw('random() as column_275664'));
        });
        $this->assertEquals(['create view "test_787482" as select random() as column_275664'], array_column($queries, 'query'));
    }

    public function testCreateViewOrReplace(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createViewOrReplace('test_623631', DB::query()->selectRaw('random() as column_449988'));
        });
        $this->assertEquals(['create or replace view "test_623631" as select random() as column_449988'], array_column($queries, 'query'));
    }

    public function testCreateViewOrReplaceWithColumns(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createViewOrReplace('test_623632', DB::query()->selectRaw('random()'), ['column_449989']);
        });
        $this->assertEquals(['create or replace view "test_623632" ("column_449989") as select random()'], array_column($queries, 'query'));
    }

    public function testCreateViewWithColumns(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createView('test_787483', DB::query()->selectRaw('random()'), ['column_275665']);
        });
        $this->assertEquals(['create view "test_787483" ("column_275665") as select random()'], array_column($queries, 'query'));
    }

    public function testDropView(): void
    {
        DB::statement('CREATE VIEW test_125382 AS SELECT random() as column_298864');
        DB::statement('CREATE VIEW test_781693 AS SELECT random() as column_377546');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropView('test_125382', 'test_781693');
        });
        $this->assertEquals(['drop view "test_125382", "test_781693"'], array_column($queries, 'query'));
    }

    public function testDropViewIfExists(): void
    {
        DB::statement('CREATE VIEW test_450510 AS SELECT random() as column_917237');
        DB::statement('CREATE VIEW test_210779 AS SELECT random() as column_727011');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropViewIfExists('test_450510', 'test_210779');
        });
        $this->assertEquals(['drop view if exists "test_450510", "test_210779"'], array_column($queries, 'query'));
    }

    public function testMaterializedDropView(): void
    {
        DB::statement('CREATE MATERIALIZED VIEW test_227238 AS SELECT random() as column_765917');
        DB::statement('CREATE MATERIALIZED VIEW test_871404 AS SELECT random() as column_369372');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropMaterializedView('test_227238', 'test_871404');
        });
        $this->assertEquals(['drop materialized view "test_227238", "test_871404"'], array_column($queries, 'query'));
    }

    public function testMaterializedDropViewIfExists(): void
    {
        DB::statement('CREATE MATERIALIZED VIEW test_495840 AS SELECT random() as column_370555');
        DB::statement('CREATE MATERIALIZED VIEW test_739551 AS SELECT random() as column_149927');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropMaterializedViewIfExists('test_495840', 'test_739551');
        });
        $this->assertEquals(['drop materialized view if exists "test_495840", "test_739551"'], array_column($queries, 'query'));
    }

    public function testRefreshMaterializedView(): void
    {
        DB::statement('CREATE MATERIALIZED VIEW test_302056 AS SELECT random() as column_521312');
        $queries = $this->withQueryLog(function (): void {
            Schema::refreshMaterializedView('test_302056');
        });
        $this->assertEquals(['refresh materialized view "test_302056" with data'], array_column($queries, 'query'));
    }

    public function testRefreshMaterializedViewConcurrently(): void
    {
        DB::statement('CREATE MATERIALIZED VIEW test_575514 AS SELECT random() as column_292611');
        DB::statement('CREATE UNIQUE INDEX ON test_575514 (column_292611)');
        $queries = $this->withQueryLog(function (): void {
            Schema::refreshMaterializedView('test_575514', concurrently: true);
        });
        $this->assertEquals(['refresh materialized view concurrently "test_575514" with data'], array_column($queries, 'query'));
    }

    public function testRefreshMaterializedViewWithoutData(): void
    {
        DB::statement('CREATE MATERIALIZED VIEW test_640796 AS SELECT random() as column_127240');
        $queries = $this->withQueryLog(function (): void {
            Schema::refreshMaterializedView('test_640796', withData: false);
        });
        $this->assertEquals(['refresh materialized view "test_640796" with no data'], array_column($queries, 'query'));
    }
}
