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

        $ctes = [];
        foreach ($expressions as $expression) {
            $parts = [
                transform($expression['options']['recursive'] ?? null, fn ($recursive) => $recursive ? 'recursive' : 'null'),
                $this->escapeFunctionalCtes($expression['as']),
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
     * Escape the function elements
     */
    private function escapeFunctionalCtes(string $cteTableName): string
    {
        // Only escape table name if not a function
        if (!str_contains($cteTableName, '(')) return $this->wrapTable($cteTableName);

        // extract function and arguments
        [$functionName, $functionArguments] = explode('(', $cteTableName);

        $functionName = $this->wrapTable($functionName);

        // wrap every arguments
        $functionArguments = trim($functionArguments, ')');
        $functionArguments = explode(',', $functionArguments);
        $functionArguments = array_map(
            fn (string $argument) => $this->wrapTable(trim($argument)),
            $functionArguments
        );
        $functionArguments = implode(',', $functionArguments);

        return "{$functionName}({$functionArguments})";
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
