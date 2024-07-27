<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class CustomDataTypesTest extends TestCase
{
    public function testChangeTypeWithAlteration(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $queries = $this->withQueryLog(function (): void {
            Schema::changeType('chocolate_type', 'rename to choco_type');
        });
        $this->assertEquals([
            'alter type chocolate_type rename to choco_type',
        ], array_column($queries, 'query'));
    }

    public function testChangeTypeWithoutAlteration(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $queries = $this->withQueryLog(function (): void {
            Schema::changeType('chocolate_type rename to choco_type');
        });
        $this->assertEquals([
            'alter type chocolate_type rename to choco_type',
        ], array_column($queries, 'query'));
    }

    public function testChangeTypeName(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $queries = $this->withQueryLog(function (): void {
            Schema::changeTypeName('chocolate_type', 'choco_type');
        });
        $this->assertEquals([
            'alter type chocolate_type rename to choco_type',
        ], array_column($queries, 'query'));
    }

    public function testChangeTypeToAddEnumValue(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $queries = $this->withQueryLog(function (): void {
            Schema::changeTypeToAddEnumValue('chocolate_type', 'medium');
        });
        $this->assertEquals([
            "alter type chocolate_type add value if not exists 'medium'",
        ], array_column($queries, 'query'));
    }

    public function testChangeEnumTypeValueName(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $queries = $this->withQueryLog(function (): void {
            Schema::changeEnumTypeValueName('chocolate_type', 'white', 'milk');
        });
        $this->assertEquals([
            "alter type chocolate_type rename value 'white' to 'milk'",
        ], array_column($queries, 'query'));
    }

    public function testCreateType(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createType('chocolate_type', "enum('dark', 'white')");
        });
        $this->assertEquals([
            "create type chocolate_type as enum('dark', 'white')",
        ], array_column($queries, 'query'));
    }

    public function testCreateTypeWithoutSeparateTypeDefinition(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createType("chocolate_type as enum('dark', 'white')", '');
        });
        $this->assertEquals([
            "create type chocolate_type as enum('dark', 'white')",
        ], array_column($queries, 'query'));
    }

    public function testDropType(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $this->getConnection()->statement("create type chocolate_origin as enum('swiss', 'belgian')");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropType('chocolate_type', 'chocolate_origin');
        });
        $this->assertEquals(['drop type "chocolate_type", "chocolate_origin"'], array_column($queries, 'query'));
    }

    public function testDropTypeIfExists(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $this->getConnection()->statement("create type chocolate_origin as enum('swiss', 'belgian')");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropTypeIfExists('chocolate_type', 'chocolate_origin');
        });
        $this->assertEquals(['drop type if exists "chocolate_type", "chocolate_origin"'], array_column($queries, 'query'));
    }

    public function testUseType(): void
    {
        $this->getConnection()->statement("create type chocolate_type as enum('dark', 'white')");
        $queries = $this->withQueryLog(function (): void {
            Schema::create('chocolates', function (Blueprint $table): void {
                $table->integer('id');
                $table->type('chocolate_type', 'chocolate_type');
                $table->timestampTz('date');
            });
        });
        $this->assertEquals(['create table "chocolates" ("id" integer not null, "chocolate_type" chocolate_type not null, "date" timestamp(0) with time zone not null)'], array_column($queries, 'query'));
    }
}
