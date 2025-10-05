<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Expressions;

use Carbon\CarbonImmutable;
use Composer\Semver\Comparator;
use Illuminate\Support\Arr;
use Ramsey\Uuid\UuidFactory;
use Tpetry\PostgresqlEnhanced\Expressions\Uuid7;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class Uuid7Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Comparator::lessThan($this->app->version(), '10.0.0')) {
            $this->markTestSkipped('Expression support has been added in Laravel 10.x.');
        }
    }

    public function testNative(): void
    {
        $time = CarbonImmutable::createFromFormat('Y-m-d H:i:s.uP', '2009-04-23 14:57:17.830618-04:00');

        $this->assertEquals('uuidv7()', (new Uuid7(native: true))->getValue($this->getConnection()->getQueryGrammar()));
        $this->assertEquals("uuidv7('2009-04-23 14:57:17.830618-04:00'::timestamptz - clock_timestamp())", (new Uuid7($time, native: true))->getValue($this->getConnection()->getQueryGrammar()));
    }

    public function testReimplementedIncludesTimestampOrClock(): void
    {
        $uuidNow = (new Uuid7())->getValue($this->getConnection()->getQueryGrammar());
        $this->assertStringContainsString('statement_timestamp()', $uuidNow);

        $time = CarbonImmutable::now();
        $uuidSpecific = (new Uuid7($time))->getValue($this->getConnection()->getQueryGrammar());
        $this->assertStringContainsString($time->format('Y-m-d H:i:s.uP'), $uuidSpecific);
    }

    /**
     * Two different expression invocations would always be unique because of different time.
     * So the time is fixed to check for the randomness.
     */
    public function testReimplementedIsRandom(): void
    {
        $uuid = new Uuid7(CarbonImmutable::now());

        $value1 = $this->executeExpression($uuid)->value;
        $value2 = $this->executeExpression($uuid)->value;

        $this->assertNotEquals($value1, $value2);
    }

    public function testReimplementedTimeIncreases(): void
    {
        $uuid = new Uuid7();

        $time1 = $this->executeExpression($uuid)->time;
        usleep(50000);
        $time2 = $this->executeExpression($uuid)->time;

        $this->assertGreaterThanOrEqual(50.0, $time1->diffInMilliseconds($time2));
    }

    /**
     * @return object{time: CarbonImmutable, value: string}
     */
    private function executeExpression(Uuid7 $expression): object
    {
        $row = $this->getConnection()->query()->select($expression)->first();
        $value = Arr::first((array) $row);

        $uuid = (new UuidFactory())->fromString($value);
        throw_unless($uuid instanceof \Ramsey\Uuid\Rfc4122\UuidV7, message: "{$uuid} is not a UUIDv7");
        $time = CarbonImmutable::instance($uuid->getDateTime());

        return (object) compact('value', 'time');
    }
}
