<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Grammars\PostgresGrammar;

class Grammar extends PostgresGrammar
{
    use GrammarFullText;
    use GrammarReturning;
}
