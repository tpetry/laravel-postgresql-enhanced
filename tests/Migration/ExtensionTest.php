<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ExtensionTest extends TestCase
{
    public function testCreateExtension(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createExtension('tablefunc');
        });
        $this->assertEquals(['create extension "tablefunc"'], array_column($queries, 'query'));
    }

    public function testCreateExtensionIfNotExists(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createExtensionIfNotExists('tablefunc');
        });
        $this->assertEquals(['create extension if not exists "tablefunc"'], array_column($queries, 'query'));
    }

    public function testCreateExtensionIfNotExistsWithSchema(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS extensions');
        $queries = $this->withQueryLog(function (): void {
            Schema::createExtensionIfNotExists('tablefunc', 'extensions');
        });
        $this->assertEquals(['create extension if not exists "tablefunc" schema "extensions"'], array_column($queries, 'query'));
    }

    public function testCreateExtensionWithSchema(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS extensions');
        $queries = $this->withQueryLog(function (): void {
            Schema::createExtension('tablefunc', 'extensions');
        });
        $this->assertEquals(['create extension "tablefunc" schema "extensions"'], array_column($queries, 'query'));
    }

    public function testDropExtension(): void
    {
        DB::statement('CREATE EXTENSION tablefunc');
        DB::statement('CREATE EXTENSION fuzzystrmatch');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropExtension('tablefunc', 'fuzzystrmatch');
        });
        $this->assertEquals(['drop extension "tablefunc", "fuzzystrmatch"'], array_column($queries, 'query'));
    }

    public function testDropExtensionIfExists(): void
    {
        DB::statement('CREATE EXTENSION tablefunc');
        DB::statement('CREATE EXTENSION fuzzystrmatch');
        $queries = $this->withQueryLog(function (): void {
            Schema::dropExtensionIfExists('tablefunc', 'fuzzystrmatch');
        });
        $this->assertEquals(['drop extension if exists "tablefunc", "fuzzystrmatch"'], array_column($queries, 'query'));
    }
}
