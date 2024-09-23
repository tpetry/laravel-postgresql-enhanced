<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder;

trait GrammarOrder
{
    /**
     * Compile the query orders to an array.
     *
     * @param array $orders
     */
    protected function compileOrdersToArray(Builder $query, $orders): array
    {
        return array_map(function ($order) {
            $sql = $order['sql'] ?? "{$this->wrap($order['column'])} {$order['direction']}";

            return match ($order['nulls'] ?? null) {
                'first' => "{$sql} nulls first",
                'last' => "{$sql} nulls last",
                default => $sql,
            };
        }, $orders);
    }
}
