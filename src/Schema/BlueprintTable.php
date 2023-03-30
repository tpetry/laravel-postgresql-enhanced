<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;

trait BlueprintTable
{
    /**
     * Set a table to be (un)logged.
     */
    public function unlogged(bool $value = true): Fluent
    {
        return $this->addCommand('unlogged', compact('value'));
    }
}
