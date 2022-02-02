<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ViewTest extends TestCase
{
    public function testCreateMaterializedView(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createMaterializedView('test_787472', DB::query()->selectRaw('random() as column_275654'));
        });
        $this->assertEquals(['create view "test_787472" as select random() as column_275654'], array_column($queries, 'query'));
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

    public function testRefreshMaterializedView(): void
    {
        DB::statement('CREATE MATERIALIZED VIEW test_125383 AS SELECT random() as column_298865');
        $queries = $this->withQueryLog(function (): void {
            Schema::refreshMaterializedView('test_125383', true);
        });
        $this->assertEquals(['refresh materialized view concurrently "test_125383"'], array_column($queries, 'query'));
    }
}
