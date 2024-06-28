<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

class Ismn13Type extends BaseType
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string
    {
        return 'ismn13';
    }
}
