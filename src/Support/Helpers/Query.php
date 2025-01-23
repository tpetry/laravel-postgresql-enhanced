<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Helpers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Query
{
    /**
     * Transforms a query to it's sql representation.
     */
    public static function toSql(EloquentBuilder|QueryBuilder|string $query): string
    {
        if (\is_string($query)) {
            return $query;
        }

        return $query->getGrammar()->substituteBindingsIntoRawSql(
            $query->toSql(), $query->getConnection()->prepareBindings($query->getBindings())
        );
    }
}
