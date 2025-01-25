<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

trait GrammarTimescale
{
    /**
     * Compile a timescale action command.
     */
    public function compileTimescale(Blueprint $blueprint, Fluent $command): array
    {
        /** @var \Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\Action $action */
        $action = $command->get('action');

        return $action->getValue($this, $blueprint->getTable());
    }
}
