<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class SqlqueryTest extends TestCase
{
    public function testMakeSqlQueryFromBuilder(): void
    {
        $query = DB::query()
            ->where('col1', 'test')
            ->where('col2', true)
            ->where('col3', 42)
            ->whereRaw('col4 is distinct from ?', [null])
            ->whereRaw('col5 ?? ?', 'test');
        $this->assertEquals('select * where "col1" = \'test\' and "col2" = 1 and "col3" = 42 and col4 is distinct from null and col5 ?? \'test\'', DB::getSchemaBuilder()->makeSqlQuery($query));
    }

    public function testMakeSqlQueryFromString(): void
    {
        $this->assertEquals('select 1', DB::getSchemaBuilder()->makeSqlQuery('select 1'));
    }
}
