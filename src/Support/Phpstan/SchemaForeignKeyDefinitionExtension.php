<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Database\Schema\ForeignKeyDefinition as BaseForeignKeyDefinition;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use Tpetry\PostgresqlEnhanced\Schema\ForeignKeyDefinition;

class SchemaForeignKeyDefinitionExtension implements MethodsClassReflectionExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->reflectionProvider->getClass(ForeignKeyDefinition::class)->getNativeMethod($methodName);
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (BaseForeignKeyDefinition::class !== $classReflection->getName()) {
            return false;
        }

        return $this->reflectionProvider->getClass(ForeignKeyDefinition::class)->hasNativeMethod($methodName);
    }
}
