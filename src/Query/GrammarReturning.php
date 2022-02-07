<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder;

trait GrammarReturning
{
    /**
     * Compile a "RETURNING" clause.
     */
    public function compileReturning(Builder $query, array $columns): string
    {
        return "returning {$this->columnize($columns)}";
    }
}
