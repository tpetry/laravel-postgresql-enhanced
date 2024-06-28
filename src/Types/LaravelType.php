<?php

namespace Tpetry\PostgresqlEnhanced\Types;

interface LaravelType
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string;
}
