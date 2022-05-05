<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use Tpetry\PostgresqlEnhanced\Query\Builder;

class QueryBuilderExtension implements MethodsClassReflectionExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->reflectionProvider->getClass(Builder::class)->getNativeMethod($methodName);
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (BuilderContract::class !== $classReflection->getName() && !$classReflection->implementsInterface(BuilderContract::class)) {
            return false;
        }

        return $this->reflectionProvider->getClass(Builder::class)->hasNativeMethod($methodName);
    }
}
