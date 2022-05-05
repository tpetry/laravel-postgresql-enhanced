<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;

class SchemaBlueprintExtension implements MethodsClassReflectionExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->reflectionProvider->getClass(Blueprint::class)->getNativeMethod($methodName);
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (BaseBlueprint::class !== $classReflection->getName()) {
            return false;
        }

        return $this->reflectionProvider->getClass(Blueprint::class)->hasNativeMethod($methodName);
    }
}
