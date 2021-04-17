<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Query\Builder as QueryBuilder;
use RuntimeException;

trait BuilderSqlquery
{
    /**
     * Transforms a query to it's sql representation.
     */
    public function makeSqlQuery(QueryBuilder | string $query): string
    {
        if (\is_string($query)) {
            return $query;
        }

        $bindings = $this->getConnection()->prepareBindings($query->getBindings());
        $sql = preg_replace_callback('/(?<!\?)\?(?!\?)/', function () use (&$bindings) {
            if (0 === \count($bindings)) {
                throw new RuntimeException('Number of bindings does not match the number of placeholders');
            }

            $value = array_shift($bindings);

            return (string) match (true) {
                null === $value => 'null',
                is_numeric($value) => $value,
                default => $this->getConnection()->getPdo()->quote((string) $value),
            };
        }, $query->toSql());

        if (\count($bindings) > 0) {
            throw new RuntimeException('Number of bindings does not match the number of placeholders');
        }

        return $sql;
    }
}
