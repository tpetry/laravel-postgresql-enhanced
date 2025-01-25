<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class TierChunks extends ShowChunks
{
    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select tier_chunk(c) from {$this->getShowChunksCall($grammar, $table)} c"];
    }
}
