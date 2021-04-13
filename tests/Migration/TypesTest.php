<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Closure;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class TypesTest extends TestCase
{
    public function testBigIntegerRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->bigIntegerRange('col'),
            fnChange: fn (Blueprint $table) => $table->bigIntegerRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" int8range not null)', $queries[0]['query'] ?? null);
    }

    public function testBitTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->bit('col'),
            fnChange: fn (Blueprint $table) => $table->bit('col', 9)->change(),
        );

        $this->assertEquals('create table "test" ("col" bit(1) not null)', $queries[0]['query'] ?? null);
        $this->assertEquals('alter table test alter col type bit(9)', $queries[1]['query'] ?? null);
    }

    public function testCaseInsensitiveTextTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS citext');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->caseInsensitiveText('col'),
            fnChange: fn (Blueprint $table) => $table->caseInsensitiveText('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" citext not null)', $queries[0]['query'] ?? null);
    }

    public function testDateRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->dateRange('col'),
            fnChange: fn (Blueprint $table) => $table->dateRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" daterange not null)', $queries[0]['query'] ?? null);
    }

    public function testDecimalRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->decimalRange('col'),
            fnChange: fn (Blueprint $table) => $table->decimalRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" numrange not null)', $queries[0]['query'] ?? null);
    }

    public function testEuropeanArticleNumber13TypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->europeanArticleNumber13('col'),
            fnChange: fn (Blueprint $table) => $table->europeanArticleNumber13('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" ean13 not null)', $queries[0]['query'] ?? null);
    }

    public function testHstoreTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS hstore');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->hstore('col'),
            fnChange: fn (Blueprint $table) => $table->hstore('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" hstore not null)', $queries[0]['query'] ?? null);
    }

    public function testIntegerRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->integerRange('col'),
            fnChange: fn (Blueprint $table) => $table->integerRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" int4range not null)', $queries[0]['query'] ?? null);
    }

    public function testInternationalStandardBookNumber13TypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->internationalStandardBookNumber13('col'),
            fnChange: fn (Blueprint $table) => $table->internationalStandardBookNumber13('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" isbn13 not null)', $queries[0]['query'] ?? null);
    }

    public function testInternationalStandardBookNumberTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->internationalStandardBookNumber('col'),
            fnChange: fn (Blueprint $table) => $table->internationalStandardBookNumber('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" isbn not null)', $queries[0]['query'] ?? null);
    }

    public function testInternationalStandardMusicNumber13TypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->internationalStandardMusicNumber13('col'),
            fnChange: fn (Blueprint $table) => $table->internationalStandardMusicNumber13('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" ismn13 not null)', $queries[0]['query'] ?? null);
    }

    public function testInternationalStandardMusicNumberTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->internationalStandardMusicNumber('col'),
            fnChange: fn (Blueprint $table) => $table->internationalStandardMusicNumber('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" ismn not null)', $queries[0]['query'] ?? null);
    }

    public function testInternationalStandardSerialNumber13TypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->internationalStandardSerialNumber13('col'),
            fnChange: fn (Blueprint $table) => $table->internationalStandardSerialNumber13('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" issn13 not null)', $queries[0]['query'] ?? null);
    }

    public function testInternationaltStandardSerialNumberTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->internationalStandardSerialNumber('col'),
            fnChange: fn (Blueprint $table) => $table->internationalStandardSerialNumber('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" issn not null)', $queries[0]['query'] ?? null);
    }

    public function testIpNetworkTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->ipNetwork('col'),
            fnChange: fn (Blueprint $table) => $table->ipNetwork('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" cidr not null)', $queries[0]['query'] ?? null);
    }

    public function testLabelTreeTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS ltree');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->labelTree('col'),
            fnChange: fn (Blueprint $table) => $table->labelTree('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" ltree not null)', $queries[0]['query'] ?? null);
    }

    public function testTimestampRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->timestampRange('col'),
            fnChange: fn (Blueprint $table) => $table->timestampRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tsrange not null)', $queries[0]['query'] ?? null);
    }

    public function testTimestampTzRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->timestampTzRange('col'),
            fnChange: fn (Blueprint $table) => $table->timestampTzRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tstzrange not null)', $queries[0]['query'] ?? null);
    }

    public function testUniversalProductNumberTypeIsSupported(): void
    {
        $this->app->get('db.connection')->statement('CREATE EXTENSION IF NOT EXISTS isn');
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->universalProductNumber('col'),
            fnChange: fn (Blueprint $table) => $table->universalProductNumber('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" upc not null)', $queries[0]['query'] ?? null);
    }

    public function testVarbitTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->varbit('col'),
            fnChange: fn (Blueprint $table) => $table->varbit('col', 9)->change(),
        );

        $this->assertEquals('create table "test" ("col" varbit not null)', $queries[0]['query'] ?? null);
        $this->assertEquals('alter table test alter col type varbit(9)', $queries[1]['query'] ?? null);
    }

    public function testXmlTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->xml('col'),
            fnChange: fn (Blueprint $table) => $table->xml('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" xml not null)', $queries[0]['query'] ?? null);
    }

    protected function runMigrations(Closure $fnCreate, Closure $fnChange): array
    {
        return $this->withQueryLog(function () use ($fnCreate, $fnChange): void {
            $this->app->get('db.connection')->getSchemaBuilder()->create('test', function (Blueprint $table) use ($fnCreate): void {
                $fnCreate($table);
            });
            $this->app->get('db.connection')->getSchemaBuilder()->table('test', function (Blueprint $table) use ($fnChange): void {
                $fnChange($table);
            });
        });
    }
}
