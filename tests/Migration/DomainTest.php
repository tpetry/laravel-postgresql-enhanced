<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class DomainTest extends TestCase
{
    public function testChangeDomainConstraintBuilder(): void
    {
        $this->getConnection()->statement('create domain "gasprice" as numeric(6,3) check(VALUE >= 0)');
        $queries = $this->withQueryLog(function (): void {
            Schema::changeDomainConstraint('gasprice', fn (Builder $query) => $query->where('VALUE', '>', 0));
        });
        $this->assertEquals([
            'alter domain "gasprice" drop constraint if exists "gasprice_check"',
            'alter domain "gasprice" add constraint "gasprice_check" check(VALUE > 0)',
        ], array_column($queries, 'query'));
    }

    public function testChangeDomainConstraintNull(): void
    {
        $this->getConnection()->statement('create domain "gasprice" as numeric(6,3) check(VALUE >= 0)');
        $queries = $this->withQueryLog(function (): void {
            Schema::changeDomainConstraint('gasprice', null);
        });
        $this->assertEquals(['alter domain "gasprice" drop constraint if exists "gasprice_check"'], array_column($queries, 'query'));
    }

    public function testChangeDomainConstraintSql(): void
    {
        $this->getConnection()->statement('create domain "gasprice" as numeric(6,3) check(VALUE >= 0)');
        $queries = $this->withQueryLog(function (): void {
            Schema::changeDomainConstraint('gasprice', 'VALUE > 0');
        });
        $this->assertEquals([
            'alter domain "gasprice" drop constraint if exists "gasprice_check"',
            'alter domain "gasprice" add constraint "gasprice_check" check(VALUE > 0)',
        ], array_column($queries, 'query'));
    }

    public function testCreateDomain(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createDomain('gasprice', 'numeric(6,3)');
        });
        $this->assertEquals(['create domain "gasprice" as numeric(6,3)'], array_column($queries, 'query'));
    }

    public function testCreateDomainWithCheckBuilder(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createDomain('gasprice', 'numeric(6,3)', fn (Builder $query) => $query->where('VALUE', '>=', 0));
        });
        $this->assertEquals(['create domain "gasprice" as numeric(6,3) check(VALUE >= 0)'], array_column($queries, 'query'));
    }

    public function testCreateDomainWithCheckString(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createDomain('gasprice', 'numeric(6,3)', 'VALUE >= 0');
        });
        $this->assertEquals(['create domain "gasprice" as numeric(6,3) check(VALUE >= 0)'], array_column($queries, 'query'));
    }

    public function testDropDomain(): void
    {
        $this->getConnection()->statement('create domain "gasprice" as numeric(6,3)');
        $this->getConnection()->statement('create domain "birthdate" as date check(VALUE >= \'1900-01-01\')');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropDomain('gasprice', 'birthdate');
        });
        $this->assertEquals(['drop domain "gasprice", "birthdate"'], array_column($queries, 'query'));
    }

    public function testDropDomainIfExists(): void
    {
        $this->getConnection()->statement('create domain "gasprice" as numeric(6,3)');
        $this->getConnection()->statement('create domain "birthdate" as date check(VALUE >= \'1900-01-01\')');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropDomainIfExists('gasprice', 'birthdate');
        });
        $this->assertEquals(['drop domain if exists "gasprice", "birthdate"'], array_column($queries, 'query'));
    }

    public function testUseDomain(): void
    {
        $this->getConnection()->statement('create domain "gasprice" as numeric(6,3)');
        $queries = $this->withQueryLog(function (): void {
            Schema::create('historic_prices', function (Blueprint $table): void {
                $table->integer('id');
                $table->domain('price', 'gasprice');
                $table->timestampTz('date');
            });
        });
        $this->assertEquals(['create table "historic_prices" ("id" integer not null, "price" gasprice not null, "date" timestamp(0) with time zone not null)'], array_column($queries, 'query'));
    }
}
