<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared("
            CREATE TABLE example (
                example_id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );

            INSERT INTO example (str) VALUES ('IS7PD2jn');
        ");
    }

    public function testExecutesNothingOnPretend(): void
    {
        $this->getConnection()->pretend(function (): void {
            $queries = $this->withQueryLog(function (): void {
                $this->assertEquals([], $this->getConnection()->returningStatement('update example set str = ? where str = ? returning str', ['U71Voupu', 'IS7PD2jn']));
            });

            $this->assertEquals(['update example set str = ? where str = ? returning str'], array_column($queries, 'query'));
        });

        $this->assertEquals(1, $this->getConnection()->selectOne('SELECT COUNT(*) AS count FROM example WHERE str = ?', ['IS7PD2jn'])->count);
    }

    public function testReturnsData(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $results = $this->getConnection()->returningStatement('UPDATE example SET str = ? WHERE str = ? RETURNING str', ['U71Voupu', 'IS7PD2jn']);

            $this->assertEquals([(object) ['str' => 'U71Voupu']], $results);
        });

        $this->assertEquals(['UPDATE example SET str = ? WHERE str = ? RETURNING str'], array_column($queries, 'query'));
    }
}
