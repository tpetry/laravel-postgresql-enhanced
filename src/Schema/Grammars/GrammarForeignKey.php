<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

trait GrammarForeignKey
{
    /**
     * Compile a foreign key command.
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        foreach (['columns', 'references'] as $columnsKey) {
            $command[$columnsKey] = array_map(function (string|Expression|ExpressionContract $column) {
                if ($this->isExpression($column)) {
                    return $column;
                }

                if (str_starts_with(strtolower($column), 'period ')) {
                    $column = trim(substr($column, 7));

                    return new Expression("PERIOD {$this->wrap($column)}");
                }

                return $column;
            }, Arr::wrap($command[$columnsKey]));
        }

        $sql = parent::compileForeign($blueprint, $command);
        if ($command->get('notEnforced')) {
            $sql .= ' not enforced';
        }

        return $sql;
    }
}
