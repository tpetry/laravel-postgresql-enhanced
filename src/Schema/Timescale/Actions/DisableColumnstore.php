<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class DisableColumnstore implements Action
{
    public function getValue(Grammar $grammar, string $table): array
    {
        return ["alter table {$grammar->wrap($table)} set (timescaledb.compress = false)"];
    }
}
