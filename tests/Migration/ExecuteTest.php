<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ExecuteTest extends TestCase
{
    public function testExecute(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::execute('PLPGSQL', "BEGIN EXECUTE 'SELECT * FROM information_schema.tables'; END");
        });
        $this->assertEquals(["DO $$ BEGIN EXECUTE 'SELECT * FROM information_schema.tables'; END $$ LANGUAGE PLPGSQL;"], array_column($queries, 'query'));
    }
}
