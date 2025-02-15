<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Illuminate\Support\Arr;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class EnableCompression implements Action
{
    public function __construct(
        private string|array|null $orderBy = null,
        private string|array|null $segmentBy = null,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        $options = array_filter([
            'timescaledb.compress' => true,
            'timescaledb.compress_orderby' => $grammar->columnizeWithSuffix(Arr::wrap($this->orderBy)),
            'timescaledb.compress_segmentby' => $grammar->columnize(Arr::wrap($this->segmentBy)),
        ], fn (mixed $value) => filled($value));
        $optionsStr = implode(', ', array_map(fn (string $key, mixed $value) => "{$key} = {$grammar->escape($value)}", array_keys($options), array_values($options)));

        return ["alter table {$grammar->wrap($table)} set ({$optionsStr})"];
    }
}
