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

    public function testOrWhereAllValues(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereAllValues('val', 'ilike', ['%test686120%', '%test787542%'])->orWhereAllValues('val', 'ilike', ['%test470781%', '%test236697%'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" ilike all(array[?, ?]) or "val" ilike all(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['%test686120%', '%test787542%', '%test470781%', '%test236697%']],
            array_column($queries, 'bindings'),
        );
    }

    public function testOrWhereAnyValue(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereAnyValue('val', 'ilike', ['%test702465%', '%test825059%'])->orWhereAnyValue('val', 'ilike', ['%test237377%', '%test592812%'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" ilike any(array[?, ?]) or "val" ilike any(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['%test702465%', '%test825059%', '%test237377%', '%test592812%']],
            array_column($queries, 'bindings'),
        );
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

    public function testOrWhereBoolean(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val bool)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereBoolean('val', true)->orWhereBoolean('val', false)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" = true or "val" = false'],
            array_column($queries, 'query'),
        );
    }

    public function testOrWhereIntegerArrayMatches(): void
    {
        $this->getConnection()->unprepared('CREATE EXTENSION IF NOT EXISTS intarray');
        $this->getConnection()->unprepared('CREATE TABLE example (val integer[])');
        $this->getConnection()->unprepared('CREATE INDEX example_val ON example USING GIN (val gin__int_ops)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereIntegerArrayMatches('val', '3&4&(5|6)')->orWhereIntegerArrayMatches('val', '!7&8')->get();
        });
        $this->assertEquals(['select * from "example" where "val" @@ ? or "val" @@ ?'], array_column($queries, 'query'));
        $this->assertEquals([['3&4&(5|6)', '!7&8']], array_column($queries, 'bindings'));
    }

    public function testOrWhereLike(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (str text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereLike('str', 'ZsbBUJmR')->orWhereLike('str', '7Cc1Uf8t')->get();
            $this->getConnection()->table('example')->orWhereLike('str', 'OamekKIC', true)->orWhereLike('str', 'HmC3xURl', true)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "str" ilike ? or "str" ilike ?', 'select * from "example" where "str" like ? or "str" like ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['ZsbBUJmR', '7Cc1Uf8t'], ['OamekKIC', 'HmC3xURl']],
            array_column($queries, 'bindings'),
        );
    }

    public function testOrWhereNotLike(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (str text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereNotLike('str', 'ZsbBUJmR')->orWhereNotLike('str', '7Cc1Uf8t')->get();
            $this->getConnection()->table('example')->orWhereNotLike('str', 'OamekKIC', true)->orWhereNotLike('str', 'HmC3xURl', true)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "str" not ilike ? or "str" not ilike ?', 'select * from "example" where "str" not like ? or "str" not like ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['ZsbBUJmR', '7Cc1Uf8t'], ['OamekKIC', 'HmC3xURl']],
            array_column($queries, 'bindings'),
        );
    }

    public function testOrWhereNotAllValues(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereNotAllValues('val', 'ilike', ['leading793297%', '%trailing477609'])->orWhereNotAllValues('val', 'ilike', ['leading737659%', '%trailing474646'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where not "val" ilike all(array[?, ?]) or not "val" ilike all(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['leading793297%', '%trailing477609', 'leading737659%', '%trailing474646']],
            array_column($queries, 'bindings'),
        );
    }

    public function testOrWhereNotAnyValue(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereNotAnyValue('val', 'ilike', ['%test475277%', '%test764076%'])->orWhereNotAnyValue('val', 'ilike', ['%test936561%', '%test250628%'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where not "val" ilike any(array[?, ?]) or not "val" ilike any(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['%test475277%', '%test764076%', '%test936561%', '%test250628%']],
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

    public function testOrWhereNotBoolean(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val bool)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orWhereNotBoolean('val', true)->orWhereNotBoolean('val', false)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" != true or "val" != false'],
            array_column($queries, 'query'),
        );
    }

    public function testWhereAllValues(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereAllValues('val', 'ilike', ['leading594111%', '%trailing359990'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" ilike all(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['leading594111%', '%trailing359990']],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereAnyValue(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereAnyValue('val', 'ilike', ['%test157552%', '%test419109%'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" ilike any(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['%test157552%', '%test419109%']],
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

    public function testWhereBoolean(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val bool)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereBoolean('val', true)->whereBoolean('val', false)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" = true and "val" = false'],
            array_column($queries, 'query'),
        );
    }

    public function testWhereIntegerArrayMatches(): void
    {
        $this->getConnection()->unprepared('CREATE EXTENSION IF NOT EXISTS intarray');
        $this->getConnection()->unprepared('CREATE TABLE example (val integer[])');
        $this->getConnection()->unprepared('CREATE INDEX example_val ON example USING GIN (val gin__int_ops)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereIntegerArrayMatches('val', '3&4&(5|6)')->get();
        });
        $this->assertEquals(['select * from "example" where "val" @@ ?'], array_column($queries, 'query'));
        $this->assertEquals([['3&4&(5|6)']], array_column($queries, 'bindings'));
    }

    public function testWhereLike(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (str text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereLike('str', 'UkAymQlg')->get();
            $this->getConnection()->table('example')->whereLike('str', 'IcuC5Cqz', true)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "str" ilike ?', 'select * from "example" where "str" like ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['UkAymQlg'], ['IcuC5Cqz']],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereNotLike(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (str text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereNotLike('str', 'UkAymQlg')->get();
            $this->getConnection()->table('example')->whereNotLike('str', 'IcuC5Cqz', true)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "str" not ilike ?', 'select * from "example" where "str" not like ?'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['UkAymQlg'], ['IcuC5Cqz']],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereNotAllValues(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereNotAllValues('val', 'ilike', ['%test421400%', '%test763682%'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where not "val" ilike all(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['%test421400%', '%test763682%']],
            array_column($queries, 'bindings'),
        );
    }

    public function testWhereNotAnyValue(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val text)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereNotAnyValue('val', 'ilike', ['%test299285%', '%test449782%'])->get();
        });
        $this->assertEquals(
            ['select * from "example" where not "val" ilike any(array[?, ?])'],
            array_column($queries, 'query'),
        );
        $this->assertEquals(
            [['%test299285%', '%test449782%']],
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

    public function testWhereNotBoolean(): void
    {
        $this->getConnection()->unprepared('CREATE TABLE example (val bool)');

        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->whereNotBoolean('val', true)->whereNotBoolean('val', false)->get();
        });
        $this->assertEquals(
            ['select * from "example" where "val" != true and "val" != false'],
            array_column($queries, 'query'),
        );
    }
}
