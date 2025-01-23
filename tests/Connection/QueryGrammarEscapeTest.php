<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use RuntimeException;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class QueryGrammarEscapeTest extends TestCase
{
    public function testEscapeBinary(): void
    {
        $this->assertSame("'\\xdead00beef'::bytea", $this->getConnection()->getQueryGrammar()->escape(hex2bin('dead00beef'), true));
    }

    public function testEscapeBool(): void
    {
        $this->assertSame('true', $this->getConnection()->getQueryGrammar()->escape(true));
        $this->assertSame('false', $this->getConnection()->getQueryGrammar()->escape(false));
    }

    public function testEscapeFloat(): void
    {
        $this->assertSame('3.14159', $this->getConnection()->getQueryGrammar()->escape(3.14159));
        $this->assertSame('-3.14159', $this->getConnection()->getQueryGrammar()->escape(-3.14159));
    }

    public function testEscapeInt(): void
    {
        $this->assertSame('42', $this->getConnection()->getQueryGrammar()->escape(42));
        $this->assertSame('-6', $this->getConnection()->getQueryGrammar()->escape(-6));
    }

    public function testEscapeNull(): void
    {
        $this->assertSame('null', $this->getConnection()->getQueryGrammar()->escape(null));
        $this->assertSame('null', $this->getConnection()->getQueryGrammar()->escape(null, true));
    }

    public function testEscapeString(): void
    {
        $this->assertSame("'2147483647'", $this->getConnection()->getQueryGrammar()->escape('2147483647'));
        $this->assertSame("'true'", $this->getConnection()->getQueryGrammar()->escape('true'));
        $this->assertSame("'false'", $this->getConnection()->getQueryGrammar()->escape('false'));
        $this->assertSame("'null'", $this->getConnection()->getQueryGrammar()->escape('null'));
        $this->assertSame("'Hello''World'", $this->getConnection()->getQueryGrammar()->escape("Hello'World"));
    }

    public function testEscapeStringInvalidUtf8(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getConnection()->getQueryGrammar()->escape("I am hiding an invalid \x80 utf-8 continuation byte");
    }

    public function testEscapeStringNullByte(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getConnection()->getQueryGrammar()->escape("I am hiding a \00 byte");
    }
}
