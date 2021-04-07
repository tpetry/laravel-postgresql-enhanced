<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Closure;
use Illuminate\Database\Schema\PostgresBuilder;

class Builder extends PostgresBuilder
{
    /**
     * Create a new command set with a Closure.
     *
     * @param string $table
     */
    protected function createBlueprint($table, Closure $callback = null): Blueprint
    {
        return new Blueprint($table, $callback);
    }
}
