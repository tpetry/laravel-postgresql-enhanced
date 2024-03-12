<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Composer\Semver\Comparator;
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Casts\IntegerArrayCast;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class IntegerArrayCastTest extends TestCase
{
    public function testParseFromDatabaseValue(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.0.0')) {
            $this->markTestSkipped('Cast classes have been added with Laravel 8.');
        }

        $cast = new IntegerArrayCast();
        $model = new class() extends Model { };

        $this->assertNull($cast->get($model, 'column', null, []));
        $this->assertEquals([], $cast->get($model, 'column', '{}', []));
        $this->assertEquals([1, 2, 3], $cast->get($model, 'column', '{1,2,3}', []));
        $this->assertEquals([[1, 2, 3], [4, 5, 6]], $cast->get($model, 'column', '{{1,2,3},{4,5,6}}', []));
    }

    public function testTransformToDatabaseValue(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.0.0')) {
            $this->markTestSkipped('Cast classes have been added with Laravel 8.');
        }

        $cast = new IntegerArrayCast();
        $model = new class() extends Model { };

        $this->assertNull($cast->get($model, 'column', null, []));

        $this->assertEquals('{}', $cast->set($model, 'column', [], []));
        $this->assertEquals('{1,2,3}', $cast->set($model, 'column', [1, 2, 3], []));
        $this->assertEquals('{{1,2,3},{4,5,6}}', $cast->set($model, 'column', [[1, 2, 3], [4, 5, 6]], []));

        $this->assertEquals('{}', $cast->set($model, 'column', collect(), []));
        $this->assertEquals('{1,2,3}', $cast->set($model, 'column', collect([1, 2, 3]), []));
        $this->assertEquals('{{1,2,3},{4,5,6}}', $cast->set($model, 'column', collect([collect([1, 2, 3]), collect([4, 5, 6])]), []));
    }
}
