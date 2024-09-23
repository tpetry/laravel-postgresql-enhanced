<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class OrderTest extends TestCase
{
    public function testNullsDefault(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (col int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orderBy('col')->get();
            $this->getConnection()->table('example')->orderBy('col', nulls: 'default')->get();
        });
        $this->assertEquals(
            ['select * from "example" order by "col" asc', 'select * from "example" order by "col" asc'],
            array_column($queries, 'query'),
        );
    }

    public function testNullsFirst(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (col int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orderBy('col', nulls: 'first')->get();
            $this->getConnection()->table('example')->orderByNullsFirst('col')->get();
        });
        $this->assertEquals(
            ['select * from "example" order by "col" asc nulls first', 'select * from "example" order by "col" asc nulls first'],
            array_column($queries, 'query'),
        );
    }

    public function testNullsLast(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (col int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orderBy('col', nulls: 'last')->get();
            $this->getConnection()->table('example')->orderByNullsLast('col')->get();
        });
        $this->assertEquals(
            ['select * from "example" order by "col" asc nulls last', 'select * from "example" order by "col" asc nulls last'],
            array_column($queries, 'query'),
        );
    }

    public function testVectorSimilarity(): void
    {
        if (!$this->getConnection()->table('pg_available_extensions')->where('name', 'vector')->exists()) {
            $this->markTestSkipped('pg_vector is not available for this PostgreSQL server.');
        }

        $this->getConnection()->unprepared('CREATE EXTENSION IF NOT EXISTS vector');
        $this->getConnection()->unprepared('CREATE TABLE example (embeddings vector(3))');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orderByVectorSimilarity('embeddings', [0.9569, 0.1113, 0.0107])->get();
            $this->getConnection()->table('example')->orderByVectorSimilarity('embeddings', [0.2098, 0.4917, 0.3225], distance: 'l2')->get();
        });
        $this->assertEquals(
            ['select * from "example" order by ("embeddings" <=> ?) asc', 'select * from "example" order by ("embeddings" <-> ?) asc'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['[0.9569,0.1113,0.0107]'], ['[0.2098,0.4917,0.3225]']],
            array_column($queries, 'bindings'),
        );
    }
}
