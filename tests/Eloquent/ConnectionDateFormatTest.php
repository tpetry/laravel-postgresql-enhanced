<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Eloquent;

use Carbon\CarbonImmutable;
use Composer\Semver\Comparator;
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Concerns\AutomaticDateFormat;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhanced;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ConnectionDateFormatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                timestamp_p6 timestamp(6) NOT NULL,
                timestamptz_p6 timestamptz(6) NOT NULL
            );
        ');
    }

    public function testAutomaticDateFormatTraitTakesPrecedence(): void
    {
        PostgresqlEnhanced::useDateFormat('Y-m-d\TH:i:s.up');

        $model = $this->createModelWithAutomaticDateFormat();

        $this->assertSame('Y-m-d H:i:sP', $model->getDateFormat());
    }

    public function testModelRoundTripKeepsMicrosecondsAndOffset(): void
    {
        // PostgreSQL returns `2023-05-05 14:49:51.123456+00`, which doesn't match the configured format. Laravel 7.0
        // added the Date::parse() fallback in asDateTime() that handles this; Laravel 6 throws instead.
        if (Comparator::lessThan($this->app->version(), '7.0.0')) {
            $this->markTestSkipped('Laravel < 7 has no date parsing fallback for values not matching the date format.');
        }

        PostgresqlEnhanced::useDateFormat('Y-m-d\TH:i:s.up');
        $this->getConnection()->statement("SET TIME ZONE 'UTC'");

        $time = CarbonImmutable::parse('2023-05-05 16:49:51.123456+02:00');

        $model = $this->createModel();
        $model->fill([
            'timestamp_p6' => $time,
            'timestamptz_p6' => $time,
        ]);
        $model->save();

        $row = $model->newQuery()->first();

        $this->assertEquals($time, $row->getAttribute('timestamptz_p6'));
        $this->assertEquals($time->shiftTimezone('UTC'), $row->getAttribute('timestamp_p6'));
    }

    public function testModelUsesConnectionDateFormat(): void
    {
        PostgresqlEnhanced::useDateFormat('Y-m-d\TH:i:s.up');
        $this->assertSame('Y-m-d\TH:i:s.up', $this->createModel()->getDateFormat());

        PostgresqlEnhanced::useDateFormat(null);
        $this->assertSame('Y-m-d H:i:s', $this->createModel()->getDateFormat());
    }

    /**
     * Create the anonymous fixture model without any date-format trait, relying on the connection's query grammar.
     */
    protected function createModel(): Model
    {
        return new class extends Model {
            public $table = 'example';
            public $timestamps = false;
            protected $casts = [
                'timestamp_p6' => 'datetime',
                'timestamptz_p6' => 'datetime',
            ];
            protected $guarded = [];
        };
    }

    /**
     * Create the anonymous fixture model using the AutomaticDateFormat trait, which must take precedence over the connection's format.
     */
    protected function createModelWithAutomaticDateFormat(): Model
    {
        return new class extends Model {
            use AutomaticDateFormat;

            public $table = 'example';
            public $timestamps = false;
            protected $casts = [
                'timestamp_p6' => 'datetime',
                'timestamptz_p6' => 'datetime',
            ];
            protected $guarded = [];
        };
    }
}
