<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class BuilderReturningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL
            );
            CREATE UNIQUE INDEX example_str ON example (str);
        ');
    }

    public function testDeleteReturningAll(): void
    {
        $example = ExampleBuilderReturning::query()->create(['str' => 'TmBZCdqd']);
        ExampleBuilderReturning::query()->create(['str' => 'BK5tSuQM']);

        $results = ExampleBuilderReturning::query()->where('str', 'TmBZCdqd')->deleteReturning();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->first());
        $this->assertTrue($example->is($results->first()));
    }

    public function testInsertOrIgnoreReturningAll(): void
    {
        $results = ExampleBuilderReturning::query()->insertOrIgnoreReturning(['str' => 'TmBZCdqd']);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->first());
        $this->assertEquals('TmBZCdqd', $results->first()->str);
    }

    public function testInsertReturningAll(): void
    {
        $results = ExampleBuilderReturning::query()->insertReturning(['str' => 'TmBZCdqd']);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->first());
        $this->assertEquals('TmBZCdqd', $results->first()->str);
    }

    public function testInsertUsingReturningAll(): void
    {
        $results = ExampleBuilderReturning::query()->insertUsingReturning(['str'], "select 'AbsQM4kp'");

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->first());
        $this->assertEquals('AbsQM4kp', $results->first()->str);
    }

    public function testUpdateOrInsertReturningInsertAll(): void
    {
        $results = ExampleBuilderReturning::query()->updateOrInsertReturning(['str' => 'XMe8AEva']);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->first());
        $this->assertEquals('XMe8AEva', $results->first()->str);
    }

    public function testUpdateReturningAll(): void
    {
        $example = ExampleBuilderReturning::query()->create(['str' => 'FawRBxNc']);

        $results = ExampleBuilderReturning::query()->updateReturning(['str' => 'TmBZCdqd']);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(1, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->first());
        $this->assertTrue($example->is($results->first()));
        $this->assertEquals('TmBZCdqd', $results->first()->str);
    }

    public function testUpsertReturningInsertAll(): void
    {
        if (version_compare($this->app->version(), '8.10.0', '<')) {
            $this->markTestSkipped('Upsert() has been added in a later Laravel version.');
        }

        $results = ExampleBuilderReturning::query()->upsertReturning([['str' => 'Dm2zecf4'], ['str' => 'P0ttyoss']], ['str'], []);

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertEquals(2, $results->count());
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->get(0));
        $this->assertInstanceOf(ExampleBuilderReturning::class, $results->get(1));
        $this->assertEquals('Dm2zecf4', $results->get(0)->str);
        $this->assertEquals('P0ttyoss', $results->get(1)->str);
    }
}

class ExampleBuilderReturning extends Model
{
    public $guarded = [];
    public $table = 'example';
    public $timestamps = false;
}
