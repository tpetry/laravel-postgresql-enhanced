<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Expression;

trait BuilderWhere
{
    /**
     * Add an or where all statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereAllValues($column, string $operator, iterable $values): static
    {
        return $this->whereAllValues($column, $operator, $values, boolean: 'or');
    }

    /**
     * Add an or where any statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereAnyValue($column, string $operator, iterable $values): static
    {
        return $this->whereAnyValue($column, $operator, $values, boolean: 'or');
    }

    /**
     * Add an or where between symmetric statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereBetweenSymmetric($column, iterable $values): static
    {
        return $this->whereBetweenSymmetric($column, $values, boolean: 'or');
    }

    /**
     * Add an or where boolean statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereBoolean($column, bool $value): static
    {
        return $this->orWhere($column, new Expression(var_export($value, true)));
    }

    /**
     * Add an or where integer array matches statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereIntegerArrayMatches($column, string $query): static
    {
        return $this->orWhere($column, '@@', $query);
    }

    /**
     * Add an "or where like" statement to the query.
     *
     * @param Expression|string $column
     * @param Expression|string $value
     * @param bool $caseSensitive
     */
    public function orWhereLike($column, $value, $caseSensitive = false): static
    {
        return $this->whereLike($column, $value, $caseSensitive, 'or', false);
    }

    /**
     * Add an or where not all statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereNotAllValues($column, string $operator, iterable $values): static
    {
        return $this->whereAllValues($column, $operator, $values, boolean: 'or', not: true);
    }

    /**
     * Add an or where not any statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereNotAnyValue($column, string $operator, iterable $values): static
    {
        return $this->whereAnyValue($column, $operator, $values, boolean: 'or', not: true);
    }

    /**
     * Add an or where not between symmetric statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereNotBetweenSymmetric($column, iterable $values): static
    {
        return $this->whereBetweenSymmetric($column, $values, boolean: 'or', not: true);
    }

    /**
     * Add an or where not boolean statement to the query.
     *
     * @param Expression|string $column
     */
    public function orWhereNotBoolean($column, bool $value): static
    {
        return $this->orWhere($column, '!=', new Expression(var_export($value, true)));
    }

    /**
     * Add a where all statement to the query.
     *
     * @param Expression|string $column
     * @param 'and'|'or' $boolean
     */
    public function whereAllValues($column, string $operator, iterable $values, string $boolean = 'and', bool $not = false): static
    {
        $type = 'all';

        $this->wheres[] = compact('type', 'column', 'operator', 'values', 'boolean', 'not');
        $this->addBinding($this->cleanBindings(collect($values)->toArray()), 'where');

        return $this;
    }

    /**
     * Add a where any statement to the query.
     *
     * @param Expression|string $column
     * @param 'and'|'or' $boolean
     */
    public function whereAnyValue($column, string $operator, iterable $values, string $boolean = 'and', bool $not = false): static
    {
        $type = 'any';

        $this->wheres[] = compact('type', 'column', 'operator', 'values', 'boolean', 'not');
        $this->addBinding($this->cleanBindings(collect($values)->toArray()), 'where');

        return $this;
    }

    /**
     * Add a where between symmetric statement to the query.
     *
     * @param Expression|string $column
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
     * Add a where boolean statement to the query.
     *
     * @param Expression|string $column
     */
    public function whereBoolean($column, bool $value): static
    {
        return $this->where($column, new Expression(var_export($value, true)));
    }

    /**
     * Add a where integer array matches statement to the query.
     *
     * @param Expression|string $column
     */
    public function whereIntegerArrayMatches($column, string $query): static
    {
        return $this->where($column, '@@', $query);
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param Expression|string $column
     * @param Expression|string $value
     * @param bool $caseSensitive
     * @param string $boolean
     * @param bool $not
     */
    public function whereLike($column, $value, $caseSensitive = false, $boolean = 'and', $not = false): static
    {
        $type = 'like';

        $this->wheres[] = compact('type', 'column', 'value', 'caseSensitive', 'boolean', 'not');
        $this->addBinding($value);

        return $this;
    }

    /**
     * Add a where not all statement to the query.
     *
     * @param Expression|string $column
     */
    public function whereNotAllValues($column, string $operator, iterable $values): static
    {
        return $this->whereAllValues($column, $operator, $values, not: true);
    }

    /**
     * Add a where not any statement to the query.
     *
     * @param Expression|string $column
     */
    public function whereNotAnyValue($column, string $operator, iterable $values): static
    {
        return $this->whereAnyValue($column, $operator, $values, not: true);
    }

    /**
     * Add a where not between symmetric statement to the query.
     *
     * @param Expression|string $column
     */
    public function whereNotBetweenSymmetric($column, iterable $values): static
    {
        return $this->whereBetweenSymmetric($column, $values, not: true);
    }

    /**
     * Add a where not boolean statement to the query.
     *
     * @param Expression|string $column
     */
    public function whereNotBoolean($column, bool $value): static
    {
        return $this->where($column, '!=', new Expression(var_export($value, true)));
    }
}
