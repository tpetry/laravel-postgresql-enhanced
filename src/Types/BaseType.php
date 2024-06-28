<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;

abstract class BaseType extends Type implements LaravelType
{
    /**
     * Gets an array of database types that map to this Doctrine type.
     */
    public function getMappedDatabaseTypes(AbstractPlatform $platform): array
    {
        return match (true) {
            $platform instanceof PostgreSQLPlatform => [$this->getSQLDeclaration([], $platform)],
            default => [],
        };
    }

    /**
     * Gets the name of this type.
     */
    abstract public function getName(): string;

    /**
     * Gets the SQL declaration snippet for a column of this type.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $this->getName();
    }
}
