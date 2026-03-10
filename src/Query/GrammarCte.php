<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

trait GrammarCte
{
    public function compileExpressions(Builder $query, array $expressions): string
    {
        if (blank($expressions)) {
            return '';
        }

        $hasRecursive = array_reduce($expressions, static function (bool $carry, array $expression): bool {
            return $carry || ($expression['options']['recursive'] ?? false);
        }, false);

        $ctes = [];
        foreach ($expressions as $expression) {
            $parts = [
                $this->wrapTable($expression['as']),
                'as',
                transform($expression['options']['materialized'] ?? null, static fn ($materialized) => $materialized ? 'materialized' : 'not materialized'),
                "({$expression['query']})",
                transform($expression['options']['search'] ?? null, static fn ($search) => "search {$search}"),
                transform($expression['options']['cycle'] ?? null, static fn ($cycle) => "cycle {$cycle}"),
            ];

            $ctes[] = implode(' ', array_filter($parts, static fn (?string $part) => filled($part)));
        }

        return match ($hasRecursive) {
            true => 'with recursive '.implode(', ', $ctes),
            false => 'with '.implode(', ', $ctes),
        };
    }

    /**
     * Prepend common table expression sql to query.
     */
    private function prependCtes(BaseBuilder $query, string $sql): string
    {
        // Some malforming code may create query builders by itself instead of getting them from the connection but
        // nevertheless it will be sqlized by this driver's grammar implementation. To not break on misbehaving
        // implementations the code has to check that a correct builder is passed to execute the CTE logic.
        if ($query instanceof Builder && filled($query->expressions)) {
            $with = $this->compileExpressions($query, $query->expressions);

            $sql = "{$with} {$sql}";
        }

        return $sql;
    }
}
