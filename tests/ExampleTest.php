<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests;

use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;

class ExampleTest extends TestCase
{
    public function testConnectionWorks(): void
    {
        $this->assertInstanceOf(PostgresEnhancedConnection::class, $this->app->get('db.connection'));
        $this->assertEquals(1, $this->app->get('db.connection')->selectOne('SELECT 1 AS column')?->column);
    }
}
