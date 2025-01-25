<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateHypertable implements Action
{
    public function __construct(
        private string $column,
        private string|int $interval,
        private ?string $partitionFunction = null,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select create_hypertable({$grammar->escape($table)}, {$this->buildRange($grammar)}, create_default_indexes => false)"];
    }

    private function buildRange(Grammar $grammar): string
    {
        $column = $grammar->escape($this->column);
        $interval = is_numeric($this->interval) ? $this->interval : "interval {$grammar->escape($this->interval)}";
        $partitionFunction = transform($this->partitionFunction, fn (string $name) => $grammar->escape($name));

        return match (filled($partitionFunction)) {
            false => "by_range({$column}, {$interval})",
            true => "by_range({$column}, {$interval}, {$partitionFunction})",
        };
    }
}
