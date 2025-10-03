<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Tpetry\PostgresqlEnhanced\Backports\GrammarBackportConstructor;
use Tpetry\PostgresqlEnhanced\Backports\GrammarBackportEscape;

class Grammar extends PostgresGrammar
{
    use GrammarBackportConstructor;
    use GrammarBackportEscape;
    use GrammarForeignKey;
    use GrammarIndex;
    use GrammarTable;
    use GrammarTimescale;
    use GrammarTrigger;
    use GrammarTypes;

    /**
     * The possible column modifiers.
     *
     * @var string[]
     */
    protected $modifiers = ['Compression', 'Collate', 'Nullable', 'Default', 'VirtualAs', 'StoredAs', 'GeneratedAs', 'Increment'];

    /**
     * Convert an array of columns with optional suffix keywords into a delimited string.
     *
     * @param array<int, string> $columns
     */
    public function columnizeWithSuffix(array $columns): string
    {
        $columns = array_map(function (string $column): string {
            $parts = explode(' ', $column, 2);

            return trim(\sprintf('%s %s', $this->wrap($parts[0]), $parts[1] ?? ''));
        }, $columns);

        return implode(', ', $columns);
    }

    /**
     * Convert an array of names into a delimited string.
     */
    public function namize(array $names)
    {
        return implode(', ', array_map([$this, 'wrap'], $names));
    }
}
