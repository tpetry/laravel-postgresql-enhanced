<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

trait BuilderWhere
{
    /**
     * Add an or where between symmetric statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     */
    public function orWhereBetweenSymmetric($column, iterable $values): static
    {
        return $this->whereBetweenSymmetric($column, $values, boolean: 'or');
    }

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
     * Add an or where not between symmetric statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     */
    public function orWhereNotBetweenSymmetric($column, iterable $values): static
    {
        return $this->whereBetweenSymmetric($column, $values, boolean: 'or', not: true);
    }

    /**
     * Add a where between symmetric statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     * @param 'and'|'or' $boolean
     */
    public function whereBetweenSymmetric($column, iterable $values, $boolean = 'and', bool $not = false): static
    {
        // The scope is implemented by calling the standard whereBetween method and hijacking the type value afterwards.
        $this->whereBetween($column, $values, $boolean, $not);
        $this->wheres[\count($this->wheres) - 1]['type'] = 'betweenSymmetric';

        return $this;
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

    /**
     * Add a where not between symmetric statement to the query.
     *
     * @param \Illuminate\Database\Query\Expression|string $column
     */
    public function whereNotBetweenSymmetric($column, iterable $values): static
    {
        return $this->whereBetweenSymmetric($column, $values, not: true);
    }
}
