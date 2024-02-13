<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Database\Schema\ColumnDefinition as BaseColumnDefinition;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use Tpetry\PostgresqlEnhanced\Schema\ColumnDefinition;

class SchemaColumnDefinitionExtension implements MethodsClassReflectionExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->reflectionProvider->getClass(ColumnDefinition::class)->getNativeMethod($methodName);
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (BaseColumnDefinition::class !== $classReflection->getName()) {
            return false;
        }

        return $this->reflectionProvider->getClass(ColumnDefinition::class)->hasNativeMethod($methodName);
    }
}
