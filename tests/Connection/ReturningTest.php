<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Composer\Semver\Comparator;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                example_id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );
        ');
    }

    public function testExecutesNothingOnPretend(): void
    {
        $this->getConnection()->table('example')->insert(['str' => '8lnreu2H']);
        $queries = $this->withQueryLog(function (): void {
            $this->assertEquals([], $this->getConnection()->returningStatement('update example set str = ? where str = ? returning str', ['IS7PD2jn', '8lnreu2H']));
        }, pretend: true);

        // The pretend mode has been changed in Laravel 10.30.0 to include the bindings in the query string
        match (Comparator::greaterThanOrEqualTo($this->app->version(), '10.30.0')) {
            true => $this->assertEquals(["update example set str = 'IS7PD2jn' where str = '8lnreu2H' returning str"], array_column($queries, 'query')),
            false => $this->assertEquals(['update example set str = ? where str = ? returning str'], array_column($queries, 'query')),
        };
        $this->assertEquals(1, $this->getConnection()->selectOne('SELECT COUNT(*) AS count FROM example WHERE str = ?', ['8lnreu2H'])->count);
    }

    public function testReturnsData(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $results = $this->getConnection()->returningStatement('INSERT INTO example (str) VALUES (?) RETURNING str', ['U71Voupu']);

            $this->assertEquals([(object) ['str' => 'U71Voupu']], $results);
        });

        $this->assertEquals(['INSERT INTO example (str) VALUES (?) RETURNING str'], array_column($queries, 'query'));
    }
}
