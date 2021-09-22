<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Support\Fluent;

trait GrammarTypes
{
    /**
     * Create the column definition for a big integer range type.
     */
    protected function typeBigIntegerRange(Fluent $column): string
    {
        return 'int8range';
    }

    /**
     * Create the column definition for a bit type.
     */
    protected function typeBit(Fluent $column): string
    {
        return "bit({$column->length})";
    }

    /**
     * Create the column definition for a case insensitive text type.
     */
    protected function typeCaseInsensitiveText(Fluent $column): string
    {
        return 'citext';
    }

    /**
     * Create the column definition for a date range type.
     */
    protected function typeDateRange(Fluent $column): string
    {
        return 'daterange';
    }

    /**
     * Create the column definition for a decimal range type.
     */
    protected function typeDecimalRange(Fluent $column): string
    {
        return 'numrange';
    }

    /**
     * Create the column definition for an european article number type.
     */
    protected function typeEuropeanArticleNumber13(Fluent $column): string
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
    protected function typeIntegerRange(Fluent $column): string
    {
        return 'int4range';
    }

    /**
     * Create the column definition for an international standard book number type.
     */
    protected function typeInternationalStandardBookNumber(Fluent $column): string
    {
        return 'isbn';
    }

    /**
     * Create the column definition for an international standard book number type.
     */
    protected function typeInternationalStandardBookNumber13(Fluent $column): string
    {
        return 'isbn13';
    }

    /**
     * Create the column definition for an international standard music number type.
     */
    protected function typeInternationalStandardMusicNumber(Fluent $column): string
    {
        return 'ismn';
    }

    /**
     * Create the column definition for an international standard music number type.
     */
    protected function typeInternationalStandardMusicNumber13(Fluent $column): string
    {
        return 'ismn13';
    }

    /**
     * Create the column definition for an international standard serial number type.
     */
    protected function typeInternationalStandardSerialNumber(Fluent $column): string
    {
        return 'issn';
    }

    /**
     * Create the column definition for an international standard serial number type.
     */
    protected function typeInternationalStandardSerialNumber13(Fluent $column): string
    {
        return 'issn13';
    }

    /**
     * Create the column definition for an ip network type.
     */
    protected function typeIpNetwork(Fluent $column): string
    {
        return 'cidr';
    }

    /**
     * Create the column definition for a label tree type.
     */
    protected function typeLabelTree(Fluent $column): string
    {
        return 'ltree';
    }

    /**
     * Create the column definition for a timestamp range type.
     */
    protected function typeTimestampRange(Fluent $column): string
    {
        return 'tsrange';
    }

    /**
     * Create the column definition for a timestamp (with time zone) range type.
     */
    protected function typeTimestampTzRange(Fluent $column): string
    {
        return 'tstzrange';
    }

    /**
     * Create the column definition for an universal product number type.
     */
    protected function typeUniversalProductNumber(Fluent $column): string
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

    /**
     * Create the column definition for a money type.
     */
    protected function typeMoney(Fluent $column): string
    {
        return 'money';
    }
}
