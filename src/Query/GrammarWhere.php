<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Exception;
use Illuminate\Database\Query\Builder;

trait GrammarWhere
{
    /**
     * Compile an "all" clause.
     *
     * @param array{column: string, not: bool, operator: string, values: array|\ArrayAccess} $where
     */
    public function whereAll(Builder $query, $where): string
    {
        if (!\in_array($where['operator'], [...$this->operators, '??'], strict: true)) {
            throw new Exception("Invalid operator '{$where['operator']}' used.");
        }

        return match ($where['not']) {
            true => "not {$this->wrap($where['column'])} {$where['operator']} all(array[{$this->parameterize($where['values'])}])",
            false => "{$this->wrap($where['column'])} {$where['operator']} all(array[{$this->parameterize($where['values'])}])",
        };
    }

    /**
     * Compile an "any" clause.
     *
     * @param array{column: string, not: bool, operator: string, values: array|\ArrayAccess} $where
     */
    public function whereAny(Builder $query, $where): string
    {
        if (!\in_array($where['operator'], [...$this->operators, '??'], strict: true)) {
            throw new Exception("Invalid operator '{$where['operator']}' used.");
        }

        return match ($where['not']) {
            true => "not {$this->wrap($where['column'])} {$where['operator']} any(array[{$this->parameterize($where['values'])}])",
            false => "{$this->wrap($where['column'])} {$where['operator']} any(array[{$this->parameterize($where['values'])}])",
        };
    }

    /**
     * Compile a "like" clause.
     *
     * @param array{caseSensitive: bool, column: string, value: mixed} $where
     */
    public function whereLike(Builder $query, $where): string
    {
        return match ($where['caseSensitive']) {
            true => "{$this->wrap($where['column'])} like {$this->parameter($where['value'])}",
            false => "{$this->wrap($where['column'])} ilike {$this->parameter($where['value'])}",
        };
    }

    /**
     * Compile a "between symmetric" where clause.
     *
     * @param array{column: string, not: bool, values: array|\ArrayAccess} $where
     */
    protected function whereBetweenSymmetric(Builder $query, array $where): string
    {
        $min = $this->parameter(\is_array($where['values']) ? reset($where['values']) : $where['values'][0]);
        $max = $this->parameter(\is_array($where['values']) ? end($where['values']) : $where['values'][1]);

        return match ($where['not']) {
            true => "{$this->wrap($where['column'])} not between symmetric {$min} and {$max}",
            false => "{$this->wrap($where['column'])} between symmetric {$min} and {$max}",
        };
    }

    /**
     * Compile a where clause comparing two columns.
     *
     * This method is called for the join compilation to build the join condition clause. To support left lateral joins
     * 'ON true' a special case for the whereColumn needs to be implemented which is never used normally because the
     * generated condition is invalid. Basically the first and second columns are null with an equal operator which
     * would result in a condition like 'ON "" = ""' and is now generated as 'ON true'.
     *
     * @param array $where
     */
    protected function whereColumn(Builder $query, $where): string
    {
        return match ([$where['first'], $where['operator'], $where['second']]) {
            [null, '=', null] => 'true',
            default => parent::whereColumn($query, $where),
        };
    }
}
