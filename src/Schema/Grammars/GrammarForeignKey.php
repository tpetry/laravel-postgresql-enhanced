<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

trait GrammarForeignKey
{
    /**
     * Compile a foreign key command.
     */
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        $sql = parent::compileForeign($blueprint, $command);
        if ($command->get('notEnforced')) {
            $sql .= ' not enforced';
        }

        return $sql;
    }
}
