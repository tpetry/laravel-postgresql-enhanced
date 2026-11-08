<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Carbon\CarbonImmutable;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhanced;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class DateFormatTest extends TestCase
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

    public function testDefaultDateFormatIsUnchanged(): void
    {
        $this->assertSame('Y-m-d H:i:s', $this->getConnection()->getQueryGrammar()->getDateFormat());
    }

    public function testGrammarReturnsConfiguredDateFormat(): void
    {
        PostgresqlEnhanced::useDateFormat('Y-m-d\TH:i:s.up');
        $this->assertSame('Y-m-d\TH:i:s.up', $this->getConnection()->getQueryGrammar()->getDateFormat());

        PostgresqlEnhanced::useDateFormat(null);
        $this->assertSame('Y-m-d H:i:s', $this->getConnection()->getQueryGrammar()->getDateFormat());
    }

    public function testPrepareBindingsFormatsDateTime(): void
    {
        PostgresqlEnhanced::useDateFormat('Y-m-d\TH:i:s.up');

        $bindings = $this->getConnection()->prepareBindings([CarbonImmutable::parse('2023-05-05 16:49:51.123456+02:00')]);

        $this->assertSame(['2023-05-05T16:49:51.123456+02:00'], $bindings);
    }

    public function testQueryBuilderRoundTripKeepsMicrosecondsAndOffset(): void
    {
        PostgresqlEnhanced::useDateFormat('Y-m-d\TH:i:s.up');
        $this->getConnection()->statement("SET TIME ZONE 'UTC'");

        $time = CarbonImmutable::parse('2023-05-05 16:49:51.123456+02:00');
        $this->getConnection()->table('example')->insert([
            'timestamp_p6' => $time,
            'timestamptz_p6' => $time,
        ]);

        $row = $this->getConnection()->table('example')->first();

        $this->assertEquals($time, CarbonImmutable::parse($row->timestamptz_p6));
        $this->assertEquals($time->shiftTimezone('UTC'), CarbonImmutable::parse($row->timestamp_p6));
    }
}
