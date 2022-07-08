<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

class Grammar extends PostgresGrammar
{
    use GrammarCte;
    use GrammarFullText;
    use GrammarReturning;
    use GrammarWhere;

    /**
     * Compile a delete statement into SQL.
     *
     * @param \Tpetry\PostgresqlEnhanced\Query\Builder $query
     */
    public function compileDelete(BaseBuilder $query): string
    {
        return $this->prependCtes($query, parent::compileDelete($query));
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param \Tpetry\PostgresqlEnhanced\Query\Builder $query
     */
    public function compileInsert(BaseBuilder $query, array $values): string
    {
        return $this->prependCtes($query, parent::compileInsert($query, $values));
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     *
     * @param \Tpetry\PostgresqlEnhanced\Query\Builder $query
     */
    public function compileInsertUsing(BaseBuilder $query, array $columns, string $sql): string
    {
        return $this->prependCtes($query, parent::compileInsertUsing($query, $columns, $sql));
    }

    /**
     * Compile a select query into SQL.
     *
     * @param \Tpetry\PostgresqlEnhanced\Query\Builder $query
     */
    public function compileSelect(BaseBuilder $query): string
    {
        return $this->prependCtes($query, parent::compileSelect($query));
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param \Tpetry\PostgresqlEnhanced\Query\Builder $query
     */
    public function compileUpdate(BaseBuilder $query, array $values): string
    {
        return $this->prependCtes($query, parent::compileUpdate($query, $values));
    }

    /**
     * Compile an update from statement into SQL.
     *
     * @param \Tpetry\PostgresqlEnhanced\Query\Builder $query
     */
    public function compileUpdateFrom(BaseBuilder $query, $values): string
    {
        return $this->prependCtes($query, parent::compileUpdateFrom($query, $values));
    }
}
