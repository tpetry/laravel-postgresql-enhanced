<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Composer\Semver\Comparator;
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Casts\JsonForceEmptyObjectAsArray;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class JsonForceEmptyObjectAsArrayCastTest extends TestCase
{
    public function testParseFromDatabaseValue(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.0.0')) {
            $this->markTestSkipped('Cast classes have been added with Laravel 8.');
        }

        $cast = new JsonForceEmptyObjectAsArray();
        $model = new class extends Model { };

        $this->assertNull($cast->get($model, 'column', null, []));
        $this->assertEquals([], $cast->get($model, 'column', '{}', []));
        $this->assertEquals(['a' => 1, 'b' => 2], $cast->get($model, 'column', '{"a":1,"b":2}', []));
        $this->assertEquals(['c' => [1, 2, 3], 'd' => [4, 5, 6]], $cast->get($model, 'column', '{"c":[1,2,3],"d":[4,5,6]}', []));
        $this->assertEquals([1, 2], $cast->get($model, 'column', '[1,2]', []));
        $this->assertEquals([[1, 2, 3], [4, 5, 6]], $cast->get($model, 'column', '[[1,2,3],[4,5,6]]', []));
    }

    public function testTransformToDatabaseValue(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.0.0')) {
            $this->markTestSkipped('Cast classes have been added with Laravel 8.');
        }

        $cast = new JsonForceEmptyObjectAsArray();
        $model = new class extends Model { };

        $this->assertNull($cast->get($model, 'column', null, []));

        $this->assertEquals('{}', $cast->set($model, 'column', [], []));
        $this->assertEquals('{"a":1,"b":2}', $cast->set($model, 'column', ['a' => 1, 'b' => 2], []));
        $this->assertEquals('{"c":[1,2,3],"d":[4,5,6]}', $cast->set($model, 'column', ['c' => [1, 2, 3], 'd' => [4, 5, 6]], []));
        $this->assertEquals('[1,2]', $cast->set($model, 'column', [1, 2], []));
        $this->assertEquals('[[1,2,3],[4,5,6]]', $cast->set($model, 'column', [[1, 2, 3], [4, 5, 6]], []));

        $this->assertEquals('{}', $cast->set($model, 'column', collect(), []));
        $this->assertEquals('{"a":1,"b":2}', $cast->set($model, 'column', collect(['a' => 1, 'b' => 2]), []));
        $this->assertEquals('{"c":[1,2,3],"d":[4,5,6]}', $cast->set($model, 'column', collect(['c' => [1, 2, 3], 'd' => [4, 5, 6]]), []));
        $this->assertEquals('[1,2]', $cast->set($model, 'column', collect([1, 2]), []));
        $this->assertEquals('[[1,2,3],[4,5,6]]', $cast->set($model, 'column', collect([[1, 2, 3], [4, 5, 6]]), []));
    }
}
