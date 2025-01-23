<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use RuntimeException;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class SchemaGrammarEscapeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->useDefaultSchemaGrammar();
    }

    public function testEscapeBinary(): void
    {
        $this->assertSame("'\\xdead00beef'::bytea", $this->getConnection()->getSchemaGrammar()->escape(hex2bin('dead00beef'), true));
    }

    public function testEscapeBool(): void
    {
        $this->assertSame('true', $this->getConnection()->getSchemaGrammar()->escape(true));
        $this->assertSame('false', $this->getConnection()->getSchemaGrammar()->escape(false));
    }

    public function testEscapeFloat(): void
    {
        $this->assertSame('3.14159', $this->getConnection()->getSchemaGrammar()->escape(3.14159));
        $this->assertSame('-3.14159', $this->getConnection()->getSchemaGrammar()->escape(-3.14159));
    }

    public function testEscapeInt(): void
    {
        $this->assertSame('42', $this->getConnection()->getSchemaGrammar()->escape(42));
        $this->assertSame('-6', $this->getConnection()->getSchemaGrammar()->escape(-6));
    }

    public function testEscapeNull(): void
    {
        $this->assertSame('null', $this->getConnection()->getSchemaGrammar()->escape(null));
        $this->assertSame('null', $this->getConnection()->getSchemaGrammar()->escape(null, true));
    }

    public function testEscapeString(): void
    {
        $this->assertSame("'2147483647'", $this->getConnection()->getSchemaGrammar()->escape('2147483647'));
        $this->assertSame("'true'", $this->getConnection()->getSchemaGrammar()->escape('true'));
        $this->assertSame("'false'", $this->getConnection()->getSchemaGrammar()->escape('false'));
        $this->assertSame("'null'", $this->getConnection()->getSchemaGrammar()->escape('null'));
        $this->assertSame("'Hello''World'", $this->getConnection()->getSchemaGrammar()->escape("Hello'World"));
    }

    public function testEscapeStringInvalidUtf8(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getConnection()->getSchemaGrammar()->escape("I am hiding an invalid \x80 utf-8 continuation byte");
    }

    public function testEscapeStringNullByte(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getConnection()->getSchemaGrammar()->escape("I am hiding a \00 byte");
    }
}
