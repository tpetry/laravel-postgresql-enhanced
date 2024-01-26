<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;

trait GrammarTypes
{
    /**
     * Compile a change column command into a series of SQL statements.
     */
    public function compileChange(BaseBlueprint $blueprint, Fluent $command, Connection $connection): array
    {
        $queries = [];

        // The table prefix is accessed differently based on Laravel version. In old version the $prefix was public,
        // while with new ones the $blueprint->prefix() method should be used. The issue is solved by invading the
        // object and getting the property directly.
        $prefix = (fn () => $this->prefix)->call($blueprint);

        foreach ($blueprint->getChangedColumns() as $changedColumn) {
            $blueprintColumn = new BaseBlueprint($blueprint->getTable(), null, $prefix);
            $blueprintColumn->addColumn($changedColumn['type'], $changedColumn['name'], $changedColumn->toArray());

            foreach (parent::compileChange($blueprintColumn, $command, $connection) as $sql) {
                if (filled($changedColumn['using']) && Str::is('ALTER TABLE * ALTER * TYPE *', $sql)) {
                    $using = match ($connection->getSchemaGrammar()->isExpression($changedColumn['using'])) {
                        true => $connection->getSchemaGrammar()->getValue($changedColumn['using']),
                        false => $changedColumn['using'],
                    };

                    $queries[] = "{$sql} USING {$using}";
                } else {
                    $queries[] = $sql;
                }
            }

            if (filled($changedColumn['compression'])) {
                $queries[] = sprintf(
                    'ALTER TABLE %s ALTER %s SET COMPRESSION %s',
                    $this->wrapTable($blueprint->getTable()),
                    $this->wrap($changedColumn['name']),
                    $this->wrap($changedColumn['compression']),
                );
            }
        }

        return $queries;
    }

    /**
     * Get the SQL for a default column modifier.
     */
    protected function modifyCompression(Blueprint $blueprint, Fluent $column): ?string
    {
        if (filled($column['compression'])) {
            return " compression {$column['compression']}";
        }

        return null;
    }

    /**
     * Create the column definition for a bit type.
     */
    protected function typeBit(Fluent $column): string
    {
        return "bit({$column['length']})";
    }

    /**
     * Create the column definition for an ip network type.
     */
    protected function typeCidr(Fluent $column): string
    {
        return 'cidr';
    }

    /**
     * Create the column definition for a case insensitive text type.
     */
    protected function typeCitext(Fluent $column): string
    {
        return 'citext';
    }

    /**
     * Create the column definition for a date multi-range type.
     */
    protected function typeDatemultirange(Fluent $column): string
    {
        return 'datemultirange';
    }

    /**
     * Create the column definition for a date range type.
     */
    protected function typeDaterange(Fluent $column): string
    {
        return 'daterange';
    }

    /**
     * Create the column definition for a domain type.
     */
    protected function typeDomain(Fluent $column): string
    {
        return $column['domain'];
    }

    /**
     * Create the column definition for an european article number type.
     */
    protected function typeEan13(Fluent $column): string
    {
        return 'ean13';
    }

    /**
     * Create the column definition for a hstore type.
     */
    protected function typeHstore(Fluent $column): string
    {
        return 'hstore';
    }

    /**
     * Create the column definition for an integer array type.
     */
    protected function typeInt4array(Fluent $column): string
    {
        return 'integer[]';
    }

    /**
     * Create the column definition for an integer multi-range type.
     */
    protected function typeInt4multirange(Fluent $column): string
    {
        return 'int4multirange';
    }

    /**
     * Create the column definition for an integer range type.
     */
    protected function typeInt4range(Fluent $column): string
    {
        return 'int4range';
    }

    /**
     * Create the column definition for a big integer multi-range type.
     */
    protected function typeInt8multirange(Fluent $column): string
    {
        return 'int8multirange';
    }

    /**
     * Create the column definition for a big integer range type.
     */
    protected function typeInt8Range(Fluent $column): string
    {
        return 'int8range';
    }

    /**
     * Create the column definition for an international standard book number type.
     */
    protected function typeIsbn(Fluent $column): string
    {
        return 'isbn';
    }

    /**
     * Create the column definition for an international standard book number type.
     */
    protected function typeIsbn13(Fluent $column): string
    {
        return 'isbn13';
    }

    /**
     * Create the column definition for an international standard music number type.
     */
    protected function typeIsmn(Fluent $column): string
    {
        return 'ismn';
    }

    /**
     * Create the column definition for an international standard music number type.
     */
    protected function typeIsmn13(Fluent $column): string
    {
        return 'ismn13';
    }

    /**
     * Create the column definition for an international standard serial number type.
     */
    protected function typeIssn(Fluent $column): string
    {
        return 'issn';
    }

    /**
     * Create the column definition for an international standard serial number type.
     */
    protected function typeIssn13(Fluent $column): string
    {
        return 'issn13';
    }

    /**
     * Create the column definition for a label tree type.
     */
    protected function typeLtree(Fluent $column): string
    {
        return 'ltree';
    }

    /**
     * Create the column definition for a decimal multi-range type.
     */
    protected function typeNummultirange(Fluent $column): string
    {
        return 'nummultirange';
    }

    /**
     * Create the column definition for a decimal range type.
     */
    protected function typeNumrange(Fluent $column): string
    {
        return 'numrange';
    }

    /**
     * Create the column definition for a timestamp multi-range type.
     */
    protected function typeTsmultirange(Fluent $column): string
    {
        return 'tsmultirange';
    }

    /**
     * Create the column definition for a timestamp range type.
     */
    protected function typeTsrange(Fluent $column): string
    {
        return 'tsrange';
    }

    /**
     * Create the column definition for a timestamp (with time zone) multi-range type.
     */
    protected function typeTstzmultirange(Fluent $column): string
    {
        return 'tstzmultirange';
    }

    /**
     * Create the column definition for a timestamp (with time zone) range type.
     */
    protected function typeTstzrange(Fluent $column): string
    {
        return 'tstzrange';
    }

    /**
     * Create the column definition for a tsvector type.
     */
    protected function typeTsvector(Fluent $column): string
    {
        return 'tsvector';
    }

    /**
     * Create the column definition for an universal product number type.
     */
    protected function typeUpc(Fluent $column): string
    {
        return 'upc';
    }

    /**
     * Create the column definition for a varying bit type.
     */
    protected function typeVarbit(Fluent $column): string
    {
        return match (blank($column['length'])) {
            true => 'varbit',
            false => "varbit({$column['length']})",
        };
    }

    /**
     * Create the column definition for a vector type.
     */
    protected function typeVector(Fluent $column): string
    {
        return "vector({$column['dimensions']})";
    }

    /**
     * Create the column definition for a xml type.
     */
    protected function typeXml(Fluent $column): string
    {
        return 'xml';
    }
}
