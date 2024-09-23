<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Query\Expression;
use InvalidArgumentException;

trait BuilderOrder
{
    /**
     * Add an "order by" clause to the query.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<*>|\Illuminate\Contracts\Database\Query\Expression|string $column
     * @param 'asc'|'desc' $direction
     * @param 'default'|'first'|'last' $nulls
     */
    public function orderBy($column, $direction = 'asc', $nulls = 'default'): static
    {
        if ($this->isQueryable($column)) {
            [$query, $bindings] = $this->createSub($column);

            $column = new Expression('('.$query.')');

            $this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');
        }

        $direction = strtolower($direction);
        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $nulls = strtolower($nulls);
        if (!\in_array($nulls, ['default', 'first', 'last'], true)) {
            throw new InvalidArgumentException('Nulls direction must be "default", "first" or "last".');
        }

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column' => $column,
            'direction' => $direction,
            'nulls' => $nulls,
        ];

        return $this;
    }

    /**
     * Add an "order by nulls first" clause to the query.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<*>|\Illuminate\Contracts\Database\Query\Expression|string $column
     * @param 'asc'|'desc' $direction
     */
    public function orderByNullsFirst($column, string $direction = 'asc'): static
    {
        return $this->orderBy($column, $direction, 'first');
    }

    /**
     * Add an "order by nulls last" clause to the query.
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder<*>|\Illuminate\Contracts\Database\Query\Expression|string $column
     * @param 'asc'|'desc' $direction
     */
    public function orderByNullsLast($column, string $direction = 'asc'): static
    {
        return $this->orderBy($column, $direction, 'last');
    }

    /**
     * Add a vector-similarity "order by" clause to the query.
     *
     * @param Expression|string $column
     * @param array<int, float>|\Illuminate\Support\Collection<int, float> $vector
     */
    public function orderByVectorSimilarity($column, $vector, string $distance = 'cosine'): static
    {
        $operator = match ($distance) {
            'cosine' => '<=>',
            'l2' => '<->',
            default => throw new InvalidArgumentException("Unknown distance function '{$distance}'."),
        };
        $column = new Expression("({$this->getGrammar()->wrap($column)} {$operator} ?)");

        if ($vector instanceof Arrayable) {
            $vector = $vector->toArray();
        }
        $this->addBinding(json_encode($vector, flags: \JSON_THROW_ON_ERROR), $this->unions ? 'unionOrder' : 'order');

        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column' => $column,
            'direction' => 'asc',
        ];

        return $this;
    }
}
