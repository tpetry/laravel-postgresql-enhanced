<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateRefreshPolicy implements Action
{
    public function __construct(
        private string $interval,
        private string|int|null $start,
        private string|int|null $end,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        $start = $this->formatTimeSpecification($grammar, $this->start);
        $end = $this->formatTimeSpecification($grammar, $this->end);

        return ["select add_continuous_aggregate_policy({$grammar->escape($table)}, {$start}, {$end}, interval {$grammar->escape($this->interval)})"];
    }

    private function formatTimeSpecification(Grammar $grammar, string|int|null $cutoff): string
    {
        return match (true) {
            blank($cutoff) => 'null',
            is_numeric($cutoff) => (string) $cutoff,
            default => "interval {$grammar->escape($cutoff)}",
        };
    }
}
