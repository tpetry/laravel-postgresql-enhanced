<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class DisableChunkSkipping implements Action
{
    public function __construct(
        private string $column,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select disable_chunk_skipping({$grammar->escape($table)}, {$grammar->escape($this->column)})"];
    }
}
