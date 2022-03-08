<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Query;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class LazyByCursorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared("
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );
            INSERT INTO example (str) VALUES ('test1'), ('test2'), ('test3'), ('test4'), ('test5');
        ");
    }

    public function testClosesCursorOnBreak(): void
    {
        $queries = $this->withQueryLog(function (): void {
            foreach ($this->getConnection()->table('example')->orderBy('id')->lazyByCursor(2) as $row) {
                break;
            }
        });

        $cursor = preg_replace('/^declare (cursor_[^\s]+) .*$/', '$1', $queries[0]['query'] ?? '');

        $this->assertEquals(3, \count($queries));
        $this->assertEquals("declare {$cursor} no scroll cursor for select * from \"example\" order by \"id\" asc", $queries[0]['query']);
        $this->assertEquals("fetch forward 2 from {$cursor}", $queries[1]['query']);
        $this->assertEquals("close {$cursor}", $queries[2]['query']);
    }

    public function testIteratesInBatches(): void
    {
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->orderBy('id')->lazyByCursor(2)->each(function (): void {
                // do nothing 😅
            });
        });

        $cursor = preg_replace('/^declare (cursor_[a-z0-9]+).*$/', '$1', $queries[0]['query'] ?? '');

        $this->assertEquals(5, \count($queries));
        $this->assertEquals("declare {$cursor} no scroll cursor for select * from \"example\" order by \"id\" asc", $queries[0]['query']);
        $this->assertEquals("fetch forward 2 from {$cursor}", $queries[1]['query']);
        $this->assertEquals("fetch forward 2 from {$cursor}", $queries[2]['query']);
        $this->assertEquals("fetch forward 2 from {$cursor}", $queries[3]['query']);
        $this->assertEquals("close {$cursor}", $queries[4]['query']);
    }

    public function testReturnsLazyConnection(): void
    {
        $results = $this->getConnection()->table('example')->orderBy('id')->lazyByCursor();

        $this->assertEquals(5, $results->count());
        $this->assertEquals([
            (object) ['id' => 1, 'str' => 'test1'],
            (object) ['id' => 2, 'str' => 'test2'],
            (object) ['id' => 3, 'str' => 'test3'],
            (object) ['id' => 4, 'str' => 'test4'],
            (object) ['id' => 5, 'str' => 'test5'],
        ], $results->all());
    }
}
