<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class DropReorderPolicy implements Action
{
    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select remove_reorder_policy({$grammar->escape($table)})"];
    }
}
