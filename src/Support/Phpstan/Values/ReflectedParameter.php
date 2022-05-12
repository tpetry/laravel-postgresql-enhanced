<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan\Values;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\Type;

class ReflectedParameter implements ParameterReflection
{
    public function __construct(
        private string $name,
        private Type $type,
    ) {
    }

    public function getDefaultValue(): ?Type
    {
        return null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function passedByReference(): PassedByReference
    {
        return PassedByReference::createNo();
    }
}
