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
}
