<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Concerns\AutomaticDateFormatWithMilliseconds;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class AutomaticDateFormatWithMillisecondsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                timestamp_p0 timestamp(0) NOT NULL,
                timestamp_p6 timestamp(6) NOT NULL,
                timestamptz_p0 timestamptz(0) NOT NULL,
                timestamptz_p6 timestamptz(6) NOT NULL
            );
        ');
    }

    public function testAutomaticDateFormatWithMilliseconds(): void
    {
        $time = CarbonImmutable::createFromTimeString('2023-05-05 16:49:51.835916+02:00');
        $this->getConnection()->statement("SET TIME ZONE 'UTC'");

        $model = $this->createModel();
        $model->fill([
            'timestamp_p0' => $time,
            'timestamp_p6' => $time,
            'timestamptz_p0' => $time,
            'timestamptz_p6' => $time,
        ]);
        $model->save();

        $row = $model->newQuery()->first();

        $this->assertEquals($time->addSecond()->millisecond(0)->shiftTimezone('UTC'), $row->getAttribute('timestamp_p0'));
        $this->assertEquals($time->shiftTimezone('UTC'), $row->getAttribute('timestamp_p6'));
        $this->assertEquals($time->addSecond()->millisecond(0), $row->getAttribute('timestamptz_p0'));
        $this->assertEquals($time, $row->getAttribute('timestamptz_p6'));
    }

    protected function createModel(): Model
    {
        return new class() extends Model {
            use AutomaticDateFormatWithMilliseconds;

            public $table = 'example';
            public $timestamps = false;
            protected $casts = [
                'timestamp_p0' => 'datetime',
                'timestamp_p6' => 'datetime',
                'timestamptz_p0' => 'datetime',
                'timestamptz_p6' => 'datetime',
            ];
            protected $guarded = [];
        };
    }
}
