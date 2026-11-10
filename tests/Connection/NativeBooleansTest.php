<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use PDO;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhanced;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class NativeBooleansTest extends TestCase
{
    public function testBindsBooleanParameterNatively(): void
    {
        $value = $this->getConnection()->selectOne('select ?::text as value', [true])->value;
        $this->assertSame('1', $value);

        PostgresqlEnhanced::useNativeBooleans();

        $value = $this->getConnection()->selectOne('select ?::text as value', [true])->value;
        $this->assertSame('t', $value);
    }

    public function testBooleanAgainstIntegerColumnFailsWhenEnabled(): void
    {
        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['flag' => true, 'int_col' => 1]);

        $this->assertSame(1, $this->getConnection()->table('example')->where('int_col', true)->count());

        PostgresqlEnhanced::useNativeBooleans();

        $this->expectException(QueryException::class);
        $this->getConnection()->table('example')->where('int_col', true)->count();
    }

    public function testBooleanColumnRoundTripDefaultConnectionEnabled(): void
    {
        PostgresqlEnhanced::useNativeBooleans();

        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['flag' => true, 'int_col' => 1]);

        $this->assertSame(1, $this->getConnection()->table('example')->where('flag', true)->count());
        $this->assertSame(0, $this->getConnection()->table('example')->where('flag', false)->count());
    }

    public function testBooleanColumnRoundTripDefaultConnectionFlagOff(): void
    {
        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['flag' => true, 'int_col' => 1]);

        $this->assertSame(1, $this->getConnection()->table('example')->where('flag', true)->count());
        $this->assertSame(0, $this->getConnection()->table('example')->where('flag', false)->count());
    }

    public function testBooleanColumnRoundTripEmulatedConnectionEnabled(): void
    {
        PostgresqlEnhanced::useNativeBooleans();

        $this->createExampleTable('pgsql_emulated');
        $this->getConnection('pgsql_emulated')->table('example')->insert(['flag' => true, 'int_col' => 1]);

        $this->assertSame(1, $this->getConnection('pgsql_emulated')->table('example')->where('flag', true)->count());
        $this->assertSame(0, $this->getConnection('pgsql_emulated')->table('example')->where('flag', false)->count());
    }

    public function testDefaultBindingsUnchanged(): void
    {
        $bindings = $this->getConnection()->prepareBindings([1, 1.5, 'x', null, true, CarbonImmutable::parse('2023-05-05 16:49:51')]);
        $this->assertSame([1, 1.5, 'x', null, 1, '2023-05-05 16:49:51'], $bindings);

        $this->getConnection()->unprepared('
            CREATE TABLE example_values (
                int_col integer,
                float_col double precision,
                text_col text,
                nullable_col text
            );
        ');
        $this->getConnection()->table('example_values')->insert([
            'int_col' => 42,
            'float_col' => 1.5,
            'text_col' => 'x',
            'nullable_col' => null,
        ]);

        $row = $this->getConnection()->table('example_values')->first();
        $this->assertSame(42, $row->int_col);
        $this->assertSame(1.5, $row->float_col);
        $this->assertSame('x', $row->text_col);
        $this->assertNull($row->nullable_col);
    }

    public function testFlagCanBeDisabledAgain(): void
    {
        PostgresqlEnhanced::useNativeBooleans();
        PostgresqlEnhanced::useNativeBooleans(false);

        $this->assertSame([1], $this->getConnection()->prepareBindings([true]));
    }

    public function testPrepareBindingsCastsBooleansToIntegersByDefault(): void
    {
        $this->assertSame([1, 0], $this->getConnection()->prepareBindings([true, false]));
    }

    public function testPrepareBindingsKeepsBooleansWhenEnabled(): void
    {
        PostgresqlEnhanced::useNativeBooleans();

        $this->assertSame([true, false, 1, 'x'], $this->getConnection()->prepareBindings([true, false, 1, 'x']));
    }

    public function testPrepareBindingsUsesLiteralsWithEmulatedPrepares(): void
    {
        PostgresqlEnhanced::useNativeBooleans();

        $this->assertSame(['true', 'false'], $this->getConnection('pgsql_emulated')->prepareBindings([true, false]));
    }

    protected function additionalConnections(): array
    {
        return [
            'pgsql_emulated' => ['options' => [PDO::ATTR_EMULATE_PREPARES => true]],
        ];
    }

    /**
     * Create the example fixture table on the given connection.
     */
    private function createExampleTable(?string $connection = null): void
    {
        $this->getConnection($connection)->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                flag boolean NOT NULL,
                int_col integer NOT NULL DEFAULT 0
            );
        ');
    }
}
