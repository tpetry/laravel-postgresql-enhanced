<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class Int4arrayType extends BaseType
{
    /**
     * Gets the name of this type.
     */
    public function getName()
    {
        return 'int4array';
    }

    /**
     * Gets the SQL declaration snippet for a column of this type.
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return '_int4';
    }
}
