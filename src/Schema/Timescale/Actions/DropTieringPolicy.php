<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class DropTieringPolicy implements Action
{
    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select remove_tiering_policy({$grammar->escape($table)})"];
    }
}
