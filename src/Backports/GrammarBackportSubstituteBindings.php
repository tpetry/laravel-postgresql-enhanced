<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Backports;

/**
 * To support some features these commits from laravel needed to be backported for older versions:
 * - [10.x] Add toRawSql, dumpRawSql() and ddRawSql() to Query Builders (https://github.com/laravel/framework/commit/830efbeeb815a5f1558433b673a58b0ddcbdc750).
 * - [11.x] Allow for custom Postgres operators to be added (https://github.com/laravel/framework/commit/029e993cb976e76a8d34d6b175eed8c8af1c3ed8)
 */
trait GrammarBackportSubstituteBindings
{
    /**
     * The Postgres grammar specific custom operators.
     *
     * @var array
     */
    protected static $customOperators = [];

    /**
     * Set any Postgres grammar specific custom operators.
     */
    public static function customOperators(array $operators): void
    {
        static::$customOperators = array_values(
            array_merge(static::$customOperators, array_filter(array_filter($operators, 'is_string')))
        );
    }

    /**
     * Get the Postgres grammar specific operators.
     */
    public function getOperators(): array
    {
        return array_values(array_unique(array_merge($this->operators, static::$customOperators)));
    }

    /**
     * Substitute the given bindings into the given raw SQL query.
     *
     * @param string $sql
     * @param array $bindings
     */
    public function substituteBindingsIntoRawSql($sql, $bindings): string
    {
        $bindings = array_map(fn ($value) => $this->escape($value), $bindings);

        $query = '';

        $isStringLiteral = false;

        for ($i = 0; $i < \strlen($sql); ++$i) {
            $char = $sql[$i];
            $nextChar = $sql[$i + 1] ?? null;

            // Single quotes can be escaped as '' according to the SQL standard while
            // MySQL uses \'. Postgres has operators like ?| that must get encoded
            // in PHP like ??|. We should skip over the escaped characters here.
            if (\in_array($char.$nextChar, ["\'", "''", '??'])) {
                $query .= $char.$nextChar;
                ++$i;
            } elseif ("'" === $char) { // Starting / leaving string literal...
                $query .= $char;
                $isStringLiteral = !$isStringLiteral;
            } elseif ('?' === $char && !$isStringLiteral) { // Substitutable binding...
                $query .= array_shift($bindings) ?? '?';
            } else { // Normal character...
                $query .= $char;
            }
        }

        foreach ($this->getOperators() as $operator) {
            if (!str_contains($operator, '?')) {
                continue;
            }

            $query = str_replace(str_replace('?', '??', $operator), $operator, $query);
        }

        return $query;
    }
}
