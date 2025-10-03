<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Composer\Semver\Comparator;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ForeignKeyTest extends TestCase
{
    public function testNotEnforcedFalse(): void
    {
        if (Comparator::lessThan($this->getConnection()->serverVersion(), '18')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 18.');
        }

        $this->getConnection()->statement('CREATE TABLE test_589166 (col_306219 bigint PRIMARY KEY)');
        $this->getConnection()->statement('CREATE TABLE test_114824 (col_306219 bigint)');

        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_114824', function (Blueprint $table): void {
                $table->foreign('col_306219')->references('col_306219')->on('test_589166')->notEnforced(false);
            });
        });
        $this->assertEquals(['alter table "test_114824" add constraint "test_114824_col_306219_foreign" foreign key ("col_306219") references "test_589166" ("col_306219")'], array_column($queries, 'query'));
    }

    public function testNotEnforcedTrue(): void
    {
        if (Comparator::lessThan($this->getConnection()->serverVersion(), '18')) {
            $this->markTestSkipped('Null distinct handling is first supported with PostgreSQL 18.');
        }

        $this->getConnection()->statement('CREATE TABLE test_940615 (col_422395 bigint PRIMARY KEY)');
        $this->getConnection()->statement('CREATE TABLE test_861910 (col_422395 bigint)');

        $queries = $this->withQueryLog(function (): void {
            Schema::table('test_861910', function (Blueprint $table): void {
                $table->foreign('col_422395')->references('col_422395')->on('test_940615')->notEnforced(true);
            });
        });
        $this->assertEquals(['alter table "test_861910" add constraint "test_861910_col_422395_foreign" foreign key ("col_422395") references "test_940615" ("col_422395") not enforced'], array_column($queries, 'query'));
    }
}
