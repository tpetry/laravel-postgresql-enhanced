<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class WhereTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testOrWhereBetweenSymmetric(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereBetweenSymmetric('val', [926778, 391569])->orWhereBetweenSymmetric('val', [412476, 274625])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" between symmetric ? and ? or "val" between symmetric ? and ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [[926778, 391569, 412476, 274625]],
            array_column($queries, 'bindings'),
        );
    }

    public function testOrWhereLike(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (str text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereLike('str', 'ZsbBUJmR')->orWhereLike('str', '7Cc1Uf8t')->get();
            $this->getConnection()->table('example')->orWhereLike('str', 'OamekKIC', true)->orWhereLike('str', 'HmC3xURl', true)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "str" like ? or "str" like ?', 'select * from "example" where "str" ilike ? or "str" ilike ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['ZsbBUJmR', '7Cc1Uf8t'], ['OamekKIC', 'HmC3xURl']],
            array_column($queries, 'bindings'),
        );
    }

    public function testOrWhereNotBetweenSymmetric(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereNotBetweenSymmetric('val', [804722, 643565])->orWhereNotBetweenSymmetric('val', [308309, 557092])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" not between symmetric ? and ? or "val" not between symmetric ? and ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [[804722, 643565, 308309, 557092]],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereBetweenSymmetric(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereBetweenSymmetric('val', [585333, 226048])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" between symmetric ? and ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [[585333, 226048]],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereLike(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (str text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereLike('str', 'UkAymQlg')->get();
            $this->getConnection()->table('example')->whereLike('str', 'IcuC5Cqz', true)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "str" like ?', 'select * from "example" where "str" ilike ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['UkAymQlg'], ['IcuC5Cqz']],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereNotBetweenSymmetric(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val int)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereNotBetweenSymmetric('val', [762192, 196082])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" not between symmetric ? and ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [[762192, 196082]],
            array_column($queries, 'bindings'),
        );
    }
}
