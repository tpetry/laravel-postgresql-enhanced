<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Expression;

trait BuilderLateralJoin
{
    /**
     * Add a lateral subquery cross join to the query.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $query
     */
    public function crossJoinSubLateral($query, string $as): static
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = new Expression("lateral ({$query}) as {$this->grammar->wrapTable($as)}");

        $this->addBinding($bindings, 'join');

        $this->joins[] = $this->newJoinClause($this, 'cross', $expression);

        return $this;
    }

    /**
     * Add a lateral subquery join clause to the query.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string $query
     * @param \Closure|string $first
     * @param string|null $operator
     * @param string|null $second
     */
    public function joinSubLateral($query, string $as, $first, $operator = null, $second = null, string $type = 'inner', bool $where = false): static
    {
        [$query, $bindings] = $this->createSub($query);

        $expression = new Expression("lateral ({$query}) as {$this->grammar->wrapTable($as)}");

        $this->addBinding($bindings, 'join');

        return $this->join($expression, $first, $operator, $second, $type, $where);
    }

    /**
     * Add a lateral subquery left join to the query.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder|string $query
     * @param \Closure|string $first
     * @param string|null $operator
     * @param string|null $second
     */
    public function leftJoinSubLateral($query, string $as, $first, $operator = null, $second = null): static
    {
        return $this->joinSubLateral($query, $as, $first, $operator, $second, 'left');
    }
}
