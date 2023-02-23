<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Database\Query\Builder as BuilderQuery;
use Illuminate\Database\Schema\IndexDefinition;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeCombinator;
use Tpetry\PostgresqlEnhanced\Support\Phpstan\Values\ReflectedMethod;
use Tpetry\PostgresqlEnhanced\Support\Phpstan\Values\ReflectedParameter;
use UnexpectedValueException;

class SchemaIndexDefinitionExtension implements MethodsClassReflectionExtension
{
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return match ($methodName) {
            'include' => new ReflectedMethod($classReflection, $methodName, [
                $this->createFunctionVariant([new ReflectedParameter('columns', new StringType())]),
                $this->createFunctionVariant([new ReflectedParameter('columns', new ArrayType(new IntegerType(), new StringType()))]),
            ]),
            'nullsNotDistinct' => new ReflectedMethod($classReflection, $methodName, [
                $this->createFunctionVariant([]),
            ]),
            'weight' => new ReflectedMethod($classReflection, $methodName, [
                $this->createFunctionVariant([new ReflectedParameter('labels', new ArrayType(new IntegerType(), new StringType()))]),
            ]),
            'where' => new ReflectedMethod($classReflection, $methodName, [
                $this->createFunctionVariant([new ReflectedParameter('columns', new StringType())]),
                $this->createFunctionVariant([new ReflectedParameter('columns', new CallableType([new ReflectedParameter('builder', new ObjectType(BuilderContract::class))], new ObjectType(BuilderContract::class), false))]),
                $this->createFunctionVariant([new ReflectedParameter('columns', new CallableType([new ReflectedParameter('builder', new ObjectType(BuilderQuery::class))], new ObjectType(BuilderContract::class), false))]),
            ]),
            'with' => new ReflectedMethod($classReflection, $methodName, [
                $this->createFunctionVariant([new ReflectedParameter('options', new ArrayType(new StringType(), TypeCombinator::union(new BooleanType(), new FloatType(), new IntegerType(), new StringType())))]),
            ]),
            default => throw new UnexpectedValueException("'{$methodName}' is not defined for IndexDefinition."),
        };
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (IndexDefinition::class !== $classReflection->getName()) {
            return false;
        }

        return \in_array($methodName, ['include', 'nullsNotDistinct', 'weight', 'where', 'with']);
    }

    /**
     * @param array<int, \PHPStan\Reflection\ParameterReflection> $parameters
     */
    private function createFunctionVariant(array $parameters): FunctionVariant
    {
        return new FunctionVariant(
            templateTypeMap: TemplateTypeMap::createEmpty(),
            resolvedTemplateTypeMap: null,
            parameters: $parameters,
            isVariadic: false,
            returnType: new ObjectType(IndexDefinition::class),
        );
    }
}
