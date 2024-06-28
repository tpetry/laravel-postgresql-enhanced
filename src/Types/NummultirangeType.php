<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

class NummultirangeType extends BaseType
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string
    {
        return 'nummultirange';
    }
}
