<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;

class Grammar extends PostgresGrammar
{
    use GrammarTypes;

    /**
     * Convert an array of names into a delimited string.
     */
    public function namize(array $names)
    {
        return implode(', ', array_map([$this, 'wrap'], $names));
    }
}
