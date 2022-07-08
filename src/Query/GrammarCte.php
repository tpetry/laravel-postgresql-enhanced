<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

trait GrammarCte
{
    public function compileExpressions(Builder $query, array $expressions): string
    {
        if (blank($expressions)) {
            return '';
        }

        $ctes = [];
        foreach ($expressions as $expression) {
            $parts = [
                transform($expression['options']['recursive'] ?? null, fn ($recursive) => $recursive ? 'recursive' : 'null'),
                $this->wrapTable($expression['as']),
                'as',
                transform($expression['options']['materialized'] ?? null, fn ($materialized) => $materialized ? 'materialized' : 'not materialized'),
                "({$expression['query']})",
                transform($expression['options']['search'] ?? null, fn ($search) => "search {$search}"),
                transform($expression['options']['cycle'] ?? null, fn ($cycle) => "cycle {$cycle}"),
            ];

            $ctes[] = implode(' ', array_filter($parts, fn (?string $part) => filled($part)));
        }

        return 'with '.implode(', ', $ctes);
    }

    /**
     * Prepend common table expression sql to query.
     */
    private function prependCtes(Builder $query, string $sql): string
    {
        if (filled($query->expressions)) {
            $with = $this->compileExpressions($query, $query->expressions);

            $sql = "{$with} {$sql}";
        }

        return $sql;
    }
}
