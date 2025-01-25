<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateRetentionPolicy implements Action
{
    public function __construct(
        private string|int $dropAfter,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return match (is_numeric($this->dropAfter)) {
            true => ["select add_retention_policy({$grammar->escape($table)}, drop_after => {$this->dropAfter})"],
            false => ["select add_retention_policy({$grammar->escape($table)}, drop_after => interval {$grammar->escape($this->dropAfter)})"],
        };
    }
}
