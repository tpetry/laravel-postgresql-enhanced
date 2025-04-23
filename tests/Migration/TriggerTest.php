<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class TriggerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY
            );
        ');
        $this->getConnection()->unprepared('
            CREATE FUNCTION noop() RETURNS TRIGGER AS $$
                BEGIN END
            $$ LANGUAGE plpgsql;
        ');
    }

    public function testCreateTrigger(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_916042', 'noop(42)', 'before update');
                $table->trigger('noop_652445', 'noop(0815)', 'after delete');
            });
        });
        $this->assertEquals([
            'create trigger "noop_916042" before update on "example" execute function noop(42)',
            'create trigger "noop_652445" after delete on "example" execute function noop(0815)',
        ], array_column($queries, 'query'));
    }

    public function testCreateTriggerForEachRow(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_176019', 'noop()', 'after insert')
                    ->forEachRow();
            });
        });
        $this->assertEquals(['create trigger "noop_176019" after insert on "example" for each row execute function noop()'], array_column($queries, 'query'));
    }

    public function testCreateTriggerForEachStatement(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_311234', 'noop()', 'after delete')
                    ->forEachStatement();
            });
        });
        $this->assertEquals(['create trigger "noop_311234" after delete on "example" for each statement execute function noop()'], array_column($queries, 'query'));
    }

    public function testCreateTriggerReplace(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_322869', 'noop()', 'after update')
                    ->replace();
                $table->trigger('noop_485134', 'noop()', 'after update')
                    ->replace(true);
                $table->trigger('noop_277441', 'noop()', 'after update')
                    ->replace(false);
            });
        });
        $this->assertEquals([
            'create or replace trigger "noop_322869" after update on "example" execute function noop()',
            'create or replace trigger "noop_485134" after update on "example" execute function noop()',
            'create trigger "noop_277441" after update on "example" execute function noop()',
        ], array_column($queries, 'query'));
    }

    public function testCreateTriggerTransitionTables(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_989634', 'noop()', 'after update')
                    ->transitionTables();
                $table->trigger('noop_641784', 'noop()', 'after update')
                    ->transitionTables(old: 'rows_before');
                $table->trigger('noop_705489', 'noop()', 'after update')
                    ->transitionTables(new: 'rows_after');
                $table->trigger('noop_433535', 'noop()', 'after update')
                    ->transitionTables(old: 'rows_before', new: 'rows_after');
            });
        });
        $this->assertEquals([
            'create trigger "noop_989634" after update on "example" execute function noop()',
            'create trigger "noop_641784" after update on "example" referencing old table as "rows_before" execute function noop()',
            'create trigger "noop_705489" after update on "example" referencing new table as "rows_after" execute function noop()',
            'create trigger "noop_433535" after update on "example" referencing new table as "rows_after" old table as "rows_before" execute function noop()',
        ], array_column($queries, 'query'));
    }

    public function testCreateTriggerWhenBuilder(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_274029', 'noop()', 'after insert')
                    ->forEachRow()
                    ->whenCondition(fn (Builder $query) => $query->where('NEW.id', 42));
            });
        });
        $this->assertEquals(['create trigger "noop_274029" after insert on "example" for each row when (NEW."id" = 42) execute function noop()'], array_column($queries, 'query'));
    }

    public function testCreateTriggerWhenSql(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->trigger('noop_274029', 'noop()', 'after delete')
                    ->forEachRow()
                    ->whenCondition('OLD.id = 0815');
            });
        });
        $this->assertEquals(['create trigger "noop_274029" after delete on "example" for each row when (OLD.id = 0815) execute function noop()'], array_column($queries, 'query'));
    }

    public function testDropTrigger(): void
    {
        $this->getConnection()->unprepared('create trigger "noop_360231" before update on "example" execute function noop()');
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->dropTrigger('noop_360231');
            });
        });
        $this->assertEquals(['drop trigger "noop_360231" on "example"'], array_column($queries, 'query'));
    }

    public function testDropTriggerIfExists(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::table('example', function (Blueprint $table): void {
                $table->dropTriggerIfExists('noop_276055');
            });
        });
        $this->assertEquals(['drop trigger if exists "noop_276055" on "example"'], array_column($queries, 'query'));
    }
}
