<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class QueryGrammarSubstituteBindingsTest extends TestCase
{
    public function testToRawSql(): void
    {
        $query = $this->getConnection()->getQueryGrammar()->substituteBindingsIntoRawSql(
            'select * from "users" where \'{}\' ?? \'Hello\\\'\\\'World?\' AND "email" = ?',
            ['foo'],
        );

        $this->assertSame('select * from "users" where \'{}\' ? \'Hello\\\'\\\'World?\' AND "email" = \'foo\'', $query);
    }
}
