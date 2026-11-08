<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Connection;

use ReflectionMethod;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;
use Tpetry\PostgresqlEnhanced\Query\Grammar;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class SignatureCompatibilityTest extends TestCase
{
    /**
     * Laravel declares these methods without a return type, so user subclasses overriding them with Laravel's
     * signature would fatal if the package added one (php-cs-fixer's void_return would do so automatically).
     */
    public function testOverriddenLaravelMethodsHaveNoNativeReturnType(): void
    {
        $methods = [
            [PostgresEnhancedConnection::class, 'bindValues'],
            [PostgresEnhancedConnection::class, 'prepareBindings'],
            [Grammar::class, 'getDateFormat'],
        ];

        foreach ($methods as [$class, $method]) {
            $this->assertFalse((new ReflectionMethod($class, $method))->hasReturnType(), "{$class}::{$method}() must not declare a return type.");
        }
    }
}
