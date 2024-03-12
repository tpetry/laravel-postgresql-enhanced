<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Composer\Semver\Comparator;
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Casts\VectorArray;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class VectorArrayCastTest extends TestCase
{
    public function testParseFromDatabaseValue(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.0.0')) {
            $this->markTestSkipped('Cast classes have been added with Laravel 8.');
        }

        $cast = new VectorArray();
        $model = new class() extends Model { };

        $this->assertNull($cast->get($model, 'column', null, []));
        $this->assertEquals([], $cast->get($model, 'column', '[]', []));
        $this->assertEquals([0.6527, 0.7966, 0.4238], $cast->get($model, 'column', '[0.6527,0.7966,0.4238]', []));
    }

    public function testTransformToDatabaseValue(): void
    {
        if (Comparator::lessThan($this->app->version(), '8.0.0')) {
            $this->markTestSkipped('Cast classes have been added with Laravel 8.');
        }

        $cast = new VectorArray();
        $model = new class() extends Model { };

        $this->assertNull($cast->set($model, 'column', null, []));

        $this->assertEquals('[]', $cast->set($model, 'column', [], []));
        $this->assertEquals('[0.0297,0.7368,0.3378]', $cast->set($model, 'column', [0.0297, 0.7368, 0.3378], []));

        $this->assertEquals('[]', $cast->set($model, 'column', collect(), []));
        $this->assertEquals('[0.2758,0.4261,0.0427]', $cast->set($model, 'column', collect([0.2758, 0.4261, 0.0427]), []));
    }
}
