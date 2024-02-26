<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Query\Expression;
use InvalidArgumentException;

trait BuilderOrder
{
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
