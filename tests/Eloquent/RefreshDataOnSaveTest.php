<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Concerns\RefreshDataOnSave;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class RefreshDataOnSaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                str text NOT NULL,
                str_uppercase text NOT NULL GENERATED ALWAYS AS (UPPER(str)) STORED
            );
        ');
    }

    public function testRefreshOnSave(): void
    {
        $model = $this->createModel();

        $model->fill(['str' => 'test'])->save();

        $this->assertEquals([], $model->getDirty());
        $this->assertEquals([], $model->getChanges());
        $this->assertEquals(['id' => 1, 'str' => 'test', 'str_uppercase' => 'TEST'], $model->toArray());

        $model->fill(['str' => 'test2'])->save();
        $this->assertEquals([], $model->getDirty());
        $this->assertEquals(['str' => 'test2', 'str_uppercase' => 'TEST2'], $model->getChanges());
        $this->assertEquals(['id' => 1, 'str' => 'test2', 'str_uppercase' => 'TEST2'], $model->toArray());
    }

    protected function createModel(): Model
    {
        return new class extends Model {
            use RefreshDataOnSave;

            public $table = 'example';
            protected $guarded = [];
            public $timestamps = false;
        };
    }
}
