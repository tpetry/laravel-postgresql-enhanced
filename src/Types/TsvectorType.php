<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

class TsvectorType extends BaseType
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string
    {
        return 'tsvector';
    }
}
