<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder;

trait GrammarFullText
{
    /**
     * Compile a "where fulltext" clause.
     *
     * @param array $where
     */
    public function whereFullText(Builder $query, $where): string
    {
        $language = $where['options']['language'] ?? 'english';
        if (!\in_array($language, $this->validFullTextLanguages())) {
            $language = 'english';
        }

        $weights = collect($where['options']['weight'] ?? null);
        $columns = collect($where['columns'])->map(function ($column, $index) use ($language, $weights) {
            return match ($weights->has($index)) {
                true => "setweight(to_tsvector('{$language}', {$this->wrap($column)}), {$this->quoteString($weights->get($index))})",
                false => "to_tsvector('{$language}', {$this->wrap($column)})",
            };
        })->implode(' || ');

        $mode = 'plainto_tsquery';
        if (($where['options']['mode'] ?? []) === 'phrase') {
            $mode = 'phraseto_tsquery';
        }
        if (($where['options']['mode'] ?? []) === 'websearch') {
            $mode = 'websearch_to_tsquery';
        }

        return "({$columns}) @@ {$mode}('{$language}', {$this->parameter($where['value'])})";
    }
}
