<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Helpers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Query
{
    /**
     * Runs the before query callbacks of a query.
     */
    public static function applyBeforeQueryCallbacks(QueryBuilder $query): void
    {
        // @phpstan-ignore function.alreadyNarrowedType (Laravel 6 and 7 don't have the method.)
        if (method_exists($query, 'applyBeforeQueryCallbacks')) {
            $query->applyBeforeQueryCallbacks();
        }
    }

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
