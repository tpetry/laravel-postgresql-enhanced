<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateReorderPolicy implements Action
{
    public function __construct(
        private string $index,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select add_reorder_policy({$grammar->escape($table)}, {$grammar->escape($this->index)})"];
    }
}
