<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Schema\ColumnDefinition;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Tpetry\PostgresqlEnhanced\Support\Phpstan\Values\ReflectedMethod;
use Tpetry\PostgresqlEnhanced\Support\Phpstan\Values\ReflectedParameter;

class SchemaColumnDefinitionExtension implements MethodsClassReflectionExtension
{
    /**
     * @param 'compression'|'initial'|'using' $methodName
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return match ($methodName) {
            'initial' => $this->getInitialMethod($classReflection),
            'compression' => $this->getCompressionMethod($classReflection),
            'using' => $this->getUsingMethod($classReflection),
        };
    }

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (ColumnDefinition::class !== $classReflection->getName()) {
            return false;
        }

        return \in_array($methodName, ['compression', 'initial', 'using']);
    }

    private function getCompressionMethod(ClassReflection $classReflection): MethodReflection
    {
        $parameters = [new ReflectedParameter('algorithm', new StringType())];
        $returnType = new ObjectType(ColumnDefinition::class);

        return new ReflectedMethod(
            classReflection: $classReflection,
            name: 'compression',
            variants: [
                new FunctionVariant(TemplateTypeMap::createEmpty(), null, $parameters, false, $returnType),
            ],
        );
    }

    private function getInitialMethod(ClassReflection $classReflection): MethodReflection
    {
        $parameters = [new ReflectedParameter('value', new MixedType())];
        $returnType = new ObjectType(ColumnDefinition::class);

        return new ReflectedMethod(
            classReflection: $classReflection,
            name: 'initial',
            variants: [
                new FunctionVariant(TemplateTypeMap::createEmpty(), null, $parameters, false, $returnType),
            ],
        );
    }

    private function getUsingMethod(ClassReflection $classReflection): MethodReflection
    {
        $parametersExpression = [new ReflectedParameter('expression', new ObjectType(ExpressionContract::class))];
        $parametersString = [new ReflectedParameter('expression', new StringType())];
        $returnType = new ObjectType(ColumnDefinition::class);

        return new ReflectedMethod(
            classReflection: $classReflection,
            name: 'using',
            variants: [
                new FunctionVariant(TemplateTypeMap::createEmpty(), null, $parametersExpression, false, $returnType),
                new FunctionVariant(TemplateTypeMap::createEmpty(), null, $parametersString, false, $returnType),
            ],
        );
    }
}
