<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Illuminate\Database\QueryException;
use PDO;
use Tpetry\PostgresqlEnhanced\PostgresqlEnhanced;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class BinaryStringsTest extends TestCase
{
    private const INVALID_UTF8_PAYLOAD = "\xff\xfe\x01";

    private const PAYLOAD = "a\0b\xff\x00c";

    public function testBinaryStringIntoTextColumnFailsWhenEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable();

        $this->expectException(QueryException::class);
        $this->getConnection()->table('example')->insert(['text_col' => self::PAYLOAD]);
    }

    public function testBinaryStringIntoTextColumnStoresHexTextWhenEnabledEmulatedConnection(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable('pgsql_emulated');
        $this->getConnection('pgsql_emulated')->table('example')->insert(['text_col' => self::PAYLOAD]);

        // Emulated prepares don't raise an error but store the bytea hex form as text.
        $this->assertSame('\x'.bin2hex(self::PAYLOAD), $this->getConnection('pgsql_emulated')->table('example')->value('text_col'));
    }

    public function testBinaryStringIsTruncatedWhenDisabled(): void
    {
        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['blob_col' => self::PAYLOAD]);

        // pdo_pgsql silently cuts a string bound as text at the first NUL byte.
        $this->assertSame('a', $this->readBlob($this->getConnection()->table('example')->value('blob_col')));
    }

    public function testBinaryStringIsTruncatedWhenDisabledEmulatedConnection(): void
    {
        $this->createExampleTable('pgsql_emulated');
        $this->getConnection('pgsql_emulated')->table('example')->insert(['blob_col' => self::PAYLOAD]);

        $this->assertSame('a', $this->readBlob($this->getConnection('pgsql_emulated')->table('example')->value('blob_col')));
    }

    public function testBinaryStringRoundTripDefaultConnectionEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['blob_col' => self::PAYLOAD]);

        $this->assertSame(self::PAYLOAD, $this->readBlob($this->getConnection()->table('example')->value('blob_col')));
    }

    public function testBinaryStringRoundTripEmulatedConnectionEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable('pgsql_emulated');
        $this->getConnection('pgsql_emulated')->table('example')->insert(['blob_col' => self::PAYLOAD]);

        $this->assertSame(self::PAYLOAD, $this->readBlob($this->getConnection('pgsql_emulated')->table('example')->value('blob_col')));
    }

    public function testBinaryStringStaysPlainStringInQueryLog(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable();
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection()->table('example')->insert(['blob_col' => self::PAYLOAD]);
        });

        $this->assertSame([self::PAYLOAD], $queries[0]['bindings']);
    }

    public function testBinaryStringStaysPlainStringInQueryLogEmulatedConnection(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable('pgsql_emulated');
        $queries = $this->withQueryLog(function (): void {
            $this->getConnection('pgsql_emulated')->table('example')->insert(['blob_col' => self::PAYLOAD]);
        }, connection: 'pgsql_emulated');

        $this->assertSame([self::PAYLOAD], $queries[0]['bindings']);
    }

    public function testBinaryStringWhereClauseMatchesEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable();
        $this->getConnection()->table('example')->insert([['blob_col' => self::PAYLOAD], ['blob_col' => "other\0value"]]);

        $this->assertSame(1, $this->getConnection()->table('example')->where('blob_col', self::PAYLOAD)->count());
        $this->assertSame(0, $this->getConnection()->table('example')->where('blob_col', "missing\0value")->count());
    }

    public function testBinaryStringWhereClauseMatchesEnabledEmulatedConnection(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable('pgsql_emulated');
        $this->getConnection('pgsql_emulated')->table('example')->insert([['blob_col' => self::PAYLOAD], ['blob_col' => "other\0value"]]);

        $this->assertSame(1, $this->getConnection('pgsql_emulated')->table('example')->where('blob_col', self::PAYLOAD)->count());
        $this->assertSame(0, $this->getConnection('pgsql_emulated')->table('example')->where('blob_col', "missing\0value")->count());
    }

    public function testFlagCanBeDisabledAgain(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();
        PostgresqlEnhanced::detectBinaryStrings(false);

        $this->assertFalse(PostgresqlEnhanced::detectsBinaryStrings());

        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['blob_col' => self::PAYLOAD]);

        $this->assertSame('a', $this->readBlob($this->getConnection()->table('example')->value('blob_col')));
    }

    public function testInvalidUtf8StringWithoutNulRoundTripDefaultConnectionEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['blob_col' => self::INVALID_UTF8_PAYLOAD]);

        $this->assertSame(self::INVALID_UTF8_PAYLOAD, $this->readBlob($this->getConnection()->table('example')->value('blob_col')));
    }

    public function testInvalidUtf8StringWithoutNulRoundTripEmulatedConnectionEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable('pgsql_emulated');
        $this->getConnection('pgsql_emulated')->table('example')->insert(['blob_col' => self::INVALID_UTF8_PAYLOAD]);

        $this->assertSame(self::INVALID_UTF8_PAYLOAD, $this->readBlob($this->getConnection('pgsql_emulated')->table('example')->value('blob_col')));
    }

    public function testOrdinaryStringsAreUnaffectedWhenEnabled(): void
    {
        PostgresqlEnhanced::detectBinaryStrings();

        $this->createExampleTable();
        $this->getConnection()->table('example')->insert(['text_col' => 'plain text', 'blob_col' => 'plain bytes']);

        $row = $this->getConnection()->table('example')->first();
        $this->assertSame('plain text', $row->text_col);
        $this->assertSame('plain bytes', $this->readBlob($row->blob_col));
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
        $this->getConnection($connection)->unprepared('DROP TABLE IF EXISTS example');
        $this->getConnection($connection)->unprepared('
            CREATE TABLE example (
                id bigint NOT NULL GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
                text_col text,
                blob_col bytea
            );
        ');
    }

    /**
     * Read a bytea value which pdo_pgsql returns as a stream resource.
     */
    private function readBlob(mixed $value): string
    {
        return \is_resource($value) ? stream_get_contents($value) : (string) $value;
    }
}
