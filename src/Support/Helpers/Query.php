<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Helpers;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use RuntimeException;

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

        /** @var Connection $connection */
        $connection = $query->getConnection();

        $bindings = $connection->prepareBindings($query->getBindings());
        $sql = preg_replace_callback('/(?<!\?)\?(?!\?)/', function () use (&$bindings, $connection) {
            if (0 === \count($bindings)) {
                throw new RuntimeException('Number of bindings does not match the number of placeholders');
            }

            $value = array_shift($bindings);

            return (string) match (true) {
                null === $value => 'null',
                \is_bool($value) => $value ? 'true' : 'false',
                is_numeric($value) => $value,
                default => $connection->getPdo()->quote((string) $value),
            };
        }, $query->toSql());

        if (\count($bindings) > 0) {
            throw new RuntimeException('Number of bindings does not match the number of placeholders');
        }

        return $sql;
    }
}
