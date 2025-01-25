<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateTieringPolicy implements Action
{
    public function __construct(
        private string|int $moveAfter,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return match (is_numeric($this->moveAfter)) {
            true => ["select add_tiering_policy({$grammar->escape($table)}, move_after => {$this->moveAfter})"],
            false => ["select add_tiering_policy({$grammar->escape($table)}, move_after => interval {$grammar->escape($this->moveAfter)})"],
        };
    }
}
