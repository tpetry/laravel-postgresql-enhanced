<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

class Isbn13Type extends BaseType
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string
    {
        return 'isbn13';
    }
}
