<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class ChangeChunkTimeInterval implements Action
{
    public function __construct(
        private string|int $interval,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return match (is_numeric($this->interval)) {
            true => ["select set_chunk_time_interval({$grammar->escape($table)}, {$this->interval})"],
            false => ["select set_chunk_time_interval({$grammar->escape($table)}, interval '{$this->interval}')"],
        };
    }
}
