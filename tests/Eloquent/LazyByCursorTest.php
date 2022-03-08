<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Illuminate\Database\Eloquent\Model;
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
            foreach (ExampleLazyByCursor::orderBy('id')->lazyByCursor(2) as $row) {
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
            ExampleLazyByCursor::orderBy('id')->lazyByCursor(2)->each(function (): void {
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
        $results = ExampleLazyByCursor::orderBy('id')->lazyByCursor();

        $this->assertEquals(5, $results->count());
        foreach ($results->all() as $i => $result) {
            $this->assertEquals(ExampleLazyByCursor::class, $result::class);
            $this->assertEquals($i + 1, $result->id);
            $this->assertEquals("test{$result->id}", $result->str);
        }
    }
}

class ExampleLazyByCursor extends Model
{
    public $guarded = [];
    public $table = 'example';
}
