<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class FulltextSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (version_compare($this->app->version(), '8.79.0', '<')) {
            $this->markTestSkipped('Fulltext indexes have been added in a later Laraverl version.');
        }

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str1 text NOT NULL,
                str2 text NOT NULL
            );
        ');
    }

    public function testAll(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereFullText(['str1', 'str2'], 'test', ['language' => 'simple', 'mode' => 'websearch', 'weight' => ['A', 'B']])
                ->get();
        });
        $this->assertEquals(
            ['select * from "example" where (setweight(to_tsvector(\'simple\', "str1"), \'A\') || setweight(to_tsvector(\'simple\', "str2"), \'B\')) @@ websearch_to_tsquery(\'simple\', ?)'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['test']], array_column($queries, 'bindings'));
    }

    public function testBasic(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereFullText(['str1', 'str2'], 'test')
                ->get();
        });
        $this->assertEquals(
            ['select * from "example" where (to_tsvector(\'english\', "str1") || to_tsvector(\'english\', "str2")) @@ plainto_tsquery(\'english\', ?)'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['test']], array_column($queries, 'bindings'));
    }

    public function testLanguage(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereFullText(['str1', 'str2'], 'test', ['language' => 'simple'])
                ->get();
        });
        $this->assertEquals(
            ['select * from "example" where (to_tsvector(\'simple\', "str1") || to_tsvector(\'simple\', "str2")) @@ plainto_tsquery(\'simple\', ?)'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['test']], array_column($queries, 'bindings'));
    }

    public function testMode(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereFullText(['str1', 'str2'], 'test', ['mode' => 'websearch'])
                ->get();
        });
        $this->assertEquals(
            ['select * from "example" where (to_tsvector(\'english\', "str1") || to_tsvector(\'english\', "str2")) @@ websearch_to_tsquery(\'english\', ?)'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['test']], array_column($queries, 'bindings'));
    }

    public function testWeight(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()
                ->table('example')
                ->whereFullText(['str1', 'str2'], 'test', ['weight' => ['A', 'B']])
                ->get();
        });
        $this->assertEquals(
            ['select * from "example" where (setweight(to_tsvector(\'english\', "str1"), \'A\') || setweight(to_tsvector(\'english\', "str2"), \'B\')) @@ plainto_tsquery(\'english\', ?)'],
            array_column($queries, 'query'),
        );
        $this->assertEquals([['test']], array_column($queries, 'bindings'));
    }
}
