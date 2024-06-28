<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Types;

interface LaravelType
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string;
}
