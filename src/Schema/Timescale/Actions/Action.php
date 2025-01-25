<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

/**
 * @internal
 */
interface Action
{
    public function getValue(Grammar $grammar, string $table): array;
}
