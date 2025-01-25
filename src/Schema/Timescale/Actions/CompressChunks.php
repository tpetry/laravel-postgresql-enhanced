<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CompressChunks extends ShowChunks
{
    public function getValue(Grammar $grammar, string $table): array
    {
        return ["select compress_chunk(c) from {$this->getShowChunksCall($grammar, $table)} c"];
    }
}
