<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateColumnstorePolicy implements Action
{
    public function __construct(
        private string|int $compressAfter,
    ) {
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        return match (is_numeric($this->compressAfter)) {
            true => ["select add_compression_policy({$grammar->escape($table)}, compress_after => {$this->compressAfter})"],
            false => ["select add_compression_policy({$grammar->escape($table)}, compress_after => interval {$grammar->escape($this->compressAfter)})"],
        };
    }
}
