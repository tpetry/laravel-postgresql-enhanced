<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use DateTimeInterface;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

abstract class ShowChunks implements Action
{
    public function __construct(
        private DateTimeInterface|string|int|null $olderThan = null,
        private DateTimeInterface|string|int|null $newerThan = null,
    ) {
    }

    protected function getShowChunksCall(Grammar $grammar, string $table): string
    {
        $olderThan = transform($this->olderThan, fn ($time) => $this->formatTimeSpecification($grammar, $time));
        $newerThan = transform($this->newerThan, fn ($time) => $this->formatTimeSpecification($grammar, $time));

        return match ([filled($olderThan), filled($newerThan)]) {
            [false, false] => "show_chunks({$grammar->escape($table)})",
            default => value(function () use ($grammar, $table, $olderThan, $newerThan) {
                $values = array_filter([
                    'older_than' => $olderThan,
                    'newer_than' => $newerThan,
                ]);

                return "show_chunks({$grammar->escape($table)}, ".implode(', ', array_map(fn ($value, $key) => "{$key} => {$value}", $values, array_keys($values))).')';
            }),
        };
    }

    private function formatTimeSpecification(Grammar $grammar, DateTimeInterface|string|int|null $cutoff): ?string
    {
        return match (true) {
            blank($cutoff) => null,
            is_numeric($cutoff) => (string) $cutoff,
            $cutoff instanceof DateTimeInterface => $grammar->escape($cutoff->format(\DATE_ATOM)),
            default => "interval {$grammar->escape($cutoff)}",
        };
    }
}
