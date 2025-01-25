<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class DropChunks extends ShowChunks
{
    public function getValue(Grammar $grammar, string $table): array
    {
        $call = str_replace('show_chunks', 'drop_chunks', $this->getShowChunksCall($grammar, $table));

        return ["select {$call}"];
    }
}
