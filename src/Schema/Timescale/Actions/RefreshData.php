<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use DateTimeInterface;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class RefreshData implements Action
{
    public function __construct(
        private DateTimeInterface|int|null $start,
        private DateTimeInterface|int|null $end,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        $start = $this->formatTimeSpecification($grammar, $this->start);
        $end = $this->formatTimeSpecification($grammar, $this->end);

        return ["call refresh_continuous_aggregate({$grammar->escape($table)}, {$start}, {$end})"];
    }

    private function formatTimeSpecification(Grammar $grammar, DateTimeInterface|int|null $cutoff): string
    {
        return match (true) {
            is_numeric($cutoff) => (string) $cutoff,
            $cutoff instanceof DateTimeInterface => $grammar->escape($cutoff->format(\DATE_ATOM)),
            default => 'null',
        };
    }
}
