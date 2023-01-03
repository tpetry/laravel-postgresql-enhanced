<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Support\Arr;

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
        // In some cases application logic may add the same CTE multiple times because e.g. different eloquent scopes
        // add them. The app could fix it by keeping track of which CTEs have already been added. But for better
        // developer experience we just keep the last added expression, so the applications could doesn't have to be
        // made more complex when the *same* CTE is added multiple times.
        $this->expressions = array_filter($this->expressions, function (array $expression) use ($as): bool {
            return $expression['as'] !== $as;
        });
        $this->bindings['expressions'] = Arr::flatten(array_column($this->expressions, 'bindings'), 1);

        [$query, $bindings] = $this->createSub($query);
        $this->expressions[] = compact('as', 'query', 'options', 'bindings');
        $this->addBinding($bindings, 'expressions');

        return $this;
    }
}
