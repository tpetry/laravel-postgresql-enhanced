<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Tpetry\PostgresqlEnhanced\Backports\GrammarBackport;

class Grammar extends PostgresGrammar
{
    use GrammarBackport;
    use GrammarIndex;
    use GrammarTable;
    use GrammarTrigger;
    use GrammarTypes;

    /**
     * The possible column modifiers.
     *
     * @var string[]
     */
    protected $modifiers = ['Compression', 'Collate', 'Nullable', 'Default', 'VirtualAs', 'StoredAs', 'GeneratedAs', 'Increment'];

    /**
     * Convert an array of names into a delimited string.
     */
    public function namize(array $names)
    {
        return implode(', ', array_map([$this, 'wrap'], $names));
    }
}
