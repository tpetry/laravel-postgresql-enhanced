<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Closure;
use Composer\Semver\Comparator;
use Illuminate\Database\Query\Expression;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class TypesTest extends TestCase
{
    public function testBigIntegerMultiRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->bigIntegerMultiRange('col'),
            fnChange: fn (Blueprint $table) => $table->bigIntegerMultiRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" int8multirange not null)', $queries[0]['query'] ?? null);
    }

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

    public function testColumnModifierCompressionIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->string('col')->compression('pglz'),
            fnChange: fn (Blueprint $table) => $table->string('col')->compression('lz4')->change(),
        );

        $this->assertEquals('create table "test" ("col" varchar(255) compression pglz not null)', $queries[0]['query'] ?? null);
        if (Comparator::greaterThanOrEqualTo($this->app->version(), '11.x-dev')) {
            $this->assertEquals('alter table "test" alter column "col" type varchar(255), alter column "col" set not null, alter column "col" drop default, alter column "col" drop identity if exists', $queries[1]['query'] ?? null);
            $this->assertEquals('alter table "test" alter "col" set compression "lz4"', $queries[2]['query'] ?? null);
        } else {
            $this->assertEquals('alter table "test" alter "col" set compression "lz4"', $queries[1]['query'] ?? null);
        }
    }

    public function testColumnModifierUsingIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->string('col'),
            fnChange: fn (Blueprint $table) => $table->json('col')->using('json_build_array(col)')->change(),
        );

        $this->assertEquals('create table "test" ("col" varchar(255) not null)', $queries[0]['query'] ?? null);
        if (Comparator::greaterThanOrEqualTo($this->app->version(), '11.x-dev')) {
            $this->assertEquals('alter table "test" alter column "col" type json using json_build_array(col), alter column "col" set not null, alter column "col" drop default, alter column "col" drop identity if exists', $queries[1]['query'] ?? null);
        } else {
            $this->assertEquals('alter table test alter column col type JSON using json_build_array(col)', $queries[1]['query'] ?? null);
        }
    }

    public function testColumnModifierWithExpressionUsingIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->string('col'),
            fnChange: fn (Blueprint $table) => $table->json('col')->using(new Expression('json_build_array(col)'))->change(),
        );

        $this->assertEquals('create table "test" ("col" varchar(255) not null)', $queries[0]['query'] ?? null);
        if (Comparator::greaterThanOrEqualTo($this->app->version(), '11.x-dev')) {
            $this->assertEquals('alter table "test" alter column "col" type json using json_build_array(col), alter column "col" set not null, alter column "col" drop default, alter column "col" drop identity if exists', $queries[1]['query'] ?? null);
        } else {
            $this->assertEquals('alter table test alter column col type JSON using json_build_array(col)', $queries[1]['query'] ?? null);
        }
    }

    public function testDateMultiRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->dateMultiRange('col'),
            fnChange: fn (Blueprint $table) => $table->dateMultiRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" datemultirange not null)', $queries[0]['query'] ?? null);
    }

    public function testDateRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->dateRange('col'),
            fnChange: fn (Blueprint $table) => $table->dateRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" daterange not null)', $queries[0]['query'] ?? null);
    }

    public function testDecimalMultiRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->decimalMultiRange('col'),
            fnChange: fn (Blueprint $table) => $table->decimalMultiRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" nummultirange not null)', $queries[0]['query'] ?? null);
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

    public function testIdentityIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: function (Blueprint $table): void {
                $table->identity(always: true)->primary();
                $table->identity('unique');
            },
            fnChange: function (Blueprint $table): void {},
        );

        // In Laravel 10.x the SQL keyword order for generatedAs() changed.
        $this->assertContains($queries[0]['query'] ?? null, [
            'create table "test" ("id" bigint generated always as identity not null, "unique" bigint generated by default as identity not null)',
            'create table "test" ("id" bigint not null generated always as identity, "unique" bigint not null generated by default as identity)',
        ]);
    }

    public function testIntegerArrayTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->integerArray('col'),
            fnChange: fn (Blueprint $table) => $table->integerArray('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" integer[] not null)', $queries[0]['query'] ?? null);
    }

    public function testIntegerMultiRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->integerMultiRange('col'),
            fnChange: fn (Blueprint $table) => $table->integerMultiRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" int4multirange not null)', $queries[0]['query'] ?? null);
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

    public function testTimestampMultiRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->timestampMultiRange('col'),
            fnChange: fn (Blueprint $table) => $table->timestampMultiRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tsmultirange not null)', $queries[0]['query'] ?? null);
    }

    public function testTimestampRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->timestampRange('col'),
            fnChange: fn (Blueprint $table) => $table->timestampRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tsrange not null)', $queries[0]['query'] ?? null);
    }

    public function testTimestampTzMultiRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->timestampTzMultiRange('col'),
            fnChange: fn (Blueprint $table) => $table->timestampTzMultiRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tstzmultirange not null)', $queries[0]['query'] ?? null);
    }

    public function testTimestampTzRangeTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->timestampTzRange('col'),
            fnChange: fn (Blueprint $table) => $table->timestampTzRange('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tstzrange not null)', $queries[0]['query'] ?? null);
    }

    public function testTsvectorTypeIsSupported(): void
    {
        $queries = $this->runMigrations(
            fnCreate: fn (Blueprint $table) => $table->tsvector('col'),
            fnChange: fn (Blueprint $table) => $table->tsvector('col')->change(),
        );

        $this->assertEquals('create table "test" ("col" tsvector not null)', $queries[0]['query'] ?? null);
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
    }

    public function testVectorTypeIsSupported(): void
    {
        if (!$this->getConnection()->table('pg_available_extensions')->where('name', 'vector')->exists()) {
            $this->markTestSkipped('pg_vector is not available for this PostgreSQL server.');
        }

        $this->getConnection()->statement('CREATE EXTENSION IF NOT EXISTS vector');
        $queries = $this->runMigrations(
            fnCreate: function (Blueprint $table): void {
                $table->vector('col1', 1536);
                $table->vector('col2');
            },
            fnChange: function (Blueprint $table): void {
                $table->vector('col1', 1536)->change();
                $table->vector('col2')->change();
            },
        );

        $this->assertEquals('create table "test" ("col1" vector(1536) not null, "col2" vector not null)', $queries[0]['query'] ?? null);
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
            Schema::create('test', function (Blueprint $table) use ($fnCreate): void {
                $fnCreate($table);
            });
            Schema::table('test', function (Blueprint $table) use ($fnChange): void {
                $fnChange($table);
            });
        });
    }
}
