<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

trait BuilderCte
{
    /**
     * The common table expressions for the query.
     */
    public array $expressions = [];

    /**
     * Adds a common table expression to the query.
     *
     * @param \Closure|\Illuminate\Contracts\Database\Query\Builder|string $query
     * @param array{cycle?: string, materialized?: bool, recursive?: true, search?: string} $options
     */
    public function withExpression(string $as, $query, array $options = []): static
    {
        [$query, $bindings] = $this->createSub($query);

        $this->expressions[] = compact('as', 'query', 'options', 'bindings');

        $this->addBinding($bindings, 'expressions');

        return $this;
    }
}
