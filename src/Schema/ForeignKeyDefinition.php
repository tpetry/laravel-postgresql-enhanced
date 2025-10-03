<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Schema\ForeignKeyDefinition as BaseForeignKeyDefinition;

class ForeignKeyDefinition extends BaseForeignKeyDefinition
{
    /**
     * Specify whether the foreign key shouldn't be enforced (PostgreSQL).
     */
    public function notEnforced(bool $active): self
    {
        return $this;
    }
}
