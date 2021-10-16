<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;

trait GrammarTypes
{
    public function compileChange(BaseBlueprint $blueprint, Fluent $command, Connection $connection)
    {
        $queries = parent::compileChange($blueprint, $command, $connection);
        foreach ($blueprint->getChangedColumns() as $changedColumn) {
            if (null !== $changedColumn->compression) {
                $queries[] = sprintf(
                    'ALTER TABLE %s ALTER %s SET COMPRESSION %s',
                    $this->wrapTable($blueprint->getTable()),
                    $this->wrap($changedColumn->name),
                    $this->wrap($changedColumn->compression),
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
        if (null !== $column->compression) {
            return " compression {$column->compression}";
        }

        return null;
    }

    /**
     * Create the column definition for a bit type.
     */
    protected function typeBit(Fluent $column): string
    {
        return "bit({$column->length})";
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
     * Create the column definition for a date range type.
     */
    protected function typeDaterange(Fluent $column): string
    {
        return 'daterange';
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
     * Create the column definition for an integer range type.
     */
    protected function typeInt4range(Fluent $column): string
    {
        return 'int4range';
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
     * Create the column definition for a decimal range type.
     */
    protected function typeNumrange(Fluent $column): string
    {
        return 'numrange';
    }

    /**
     * Create the column definition for a timestamp range type.
     */
    protected function typeTsrange(Fluent $column): string
    {
        return 'tsrange';
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
        return match (null === $column->length) {
            true => 'varbit',
            false => "varbit({$column->length})",
        };
    }

    /**
     * Create the column definition for a xml type.
     */
    protected function typeXml(Fluent $column): string
    {
        return 'xml';
    }
}
