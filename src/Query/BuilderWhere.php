<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

trait BuilderWhere
{
    /**
     * Add an "or where like" statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     * @param \Illuminate\Database\Query\Expression|string $value
     */
    public function orWhereLike($column, $value, bool $caseInsensitive = false): static
    {
        return $this->whereLike($column, $value, $caseInsensitive, 'or');
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     * @param \Illuminate\Database\Query\Expression|string $value
     * @param string $boolean
     */
    public function whereLike($column, $value, bool $caseInsensitive = false, $boolean = 'and'): static
    {
        $type = 'like';

        $this->wheres[] = compact('type', 'column', 'value', 'caseInsensitive', 'boolean');
        $this->addBinding($value);

        return $this;
    }
}
