<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Schema\ColumnDefinition;

trait BlueprintTypes
{
    /**
     * Create a new big integer multi-range column on the table.
     */
    public function bigIntegerMultiRange(string $column): ColumnDefinition
    {
        return $this->addColumn('int8multirange', $column);
    }

    /**
     * Create a new big integer range column on the table.
     */
    public function bigIntegerRange(string $column): ColumnDefinition
    {
        return $this->addColumn('int8range', $column);
    }

    /**
     * Create a new fixed bit column on the table.
     */
    public function bit(string $column, int $length = 1): ColumnDefinition
    {
        return $this->addColumn('bit', $column, compact('length'));
    }

    /**
     * Create a new case insensitive text column on the table.
     */
    public function caseInsensitiveText(string $column): ColumnDefinition
    {
        return $this->addColumn('citext', $column);
    }

    /**
     * Create a new date multi-range column on the table.
     */
    public function dateMultiRange(string $column): ColumnDefinition
    {
        return $this->addColumn('datemultirange', $column);
    }

    /**
     * Create a new date range column on the table.
     */
    public function dateRange(string $column): ColumnDefinition
    {
        return $this->addColumn('daterange', $column);
    }

    /**
     * Create a new decimal multi-range column on the table.
     */
    public function decimalMultiRange(string $column): ColumnDefinition
    {
        return $this->addColumn('nummultirange', $column);
    }

    /**
     * Create a new decimal range column on the table.
     */
    public function decimalRange(string $column): ColumnDefinition
    {
        return $this->addColumn('numrange', $column);
    }

    /**
     * Create a new domain column on the table.
     */
    public function domain(string $column, string $type): ColumnDefinition
    {
        return $this->addColumn('domain', $column, ['domain' => $type]);
    }

    /**
     * Create a new european article number column on the table.
     */
    public function europeanArticleNumber13(string $column): ColumnDefinition
    {
        return $this->addColumn('ean13', $column);
    }

    /**
     * Create a new hstore column on the table.
     */
    public function hstore(string $column): ColumnDefinition
    {
        return $this->addColumn('hstore', $column);
    }

    /**
     * Create a new identity column on the table.
     */
    public function identity(string $column = 'id', bool $always = false): ColumnDefinition
    {
        return parent::bigInteger($column)->generatedAs()->always($always);
    }

    /**
     * Create a new integer array column on the table.
     */
    public function integerArray(string $column): ColumnDefinition
    {
        return $this->addColumn('int4array', $column);
    }

    /**
     * Create a new integer multi-range column on the table.
     */
    public function integerMultiRange(string $column): ColumnDefinition
    {
        return $this->addColumn('int4multirange', $column);
    }

    /**
     * Create a new integer range column on the table.
     */
    public function integerRange(string $column): ColumnDefinition
    {
        return $this->addColumn('int4range', $column);
    }

    /**
     * Create a new international standard book number column on the table.
     */
    public function internationalStandardBookNumber(string $column): ColumnDefinition
    {
        return $this->addColumn('isbn', $column);
    }

    /**
     * Create a new international standard book number column on the table.
     */
    public function internationalStandardBookNumber13(string $column): ColumnDefinition
    {
        return $this->addColumn('isbn13', $column);
    }

    /**
     * Create a new international standard music number column on the table.
     */
    public function internationalStandardMusicNumber(string $column): ColumnDefinition
    {
        return $this->addColumn('ismn', $column);
    }

    /**
     * Create a new international standard music number column on the table.
     */
    public function internationalStandardMusicNumber13(string $column): ColumnDefinition
    {
        return $this->addColumn('ismn13', $column);
    }

    /**
     * Create a new international standard serial number column on the table.
     */
    public function internationalStandardSerialNumber(string $column): ColumnDefinition
    {
        return $this->addColumn('issn', $column);
    }

    /**
     * Create a new international standard serial number column on the table.
     */
    public function internationalStandardSerialNumber13(string $column): ColumnDefinition
    {
        return $this->addColumn('issn13', $column);
    }

    /**
     * Create a new ip network column on the table.
     */
    public function ipNetwork(string $column): ColumnDefinition
    {
        return $this->addColumn('cidr', $column);
    }

    /**
     * Create a new case insensitive text column on the table.
     */
    public function labelTree(string $column): ColumnDefinition
    {
        return $this->addColumn('ltree', $column);
    }

    /**
     * Create a new timestamp multi-range column on the table.
     */
    public function timestampMultiRange(string $column): ColumnDefinition
    {
        return $this->addColumn('tsmultirange', $column);
    }

    /**
     * Create a new timestamp range column on the table.
     */
    public function timestampRange(string $column): ColumnDefinition
    {
        return $this->addColumn('tsrange', $column);
    }

    /**
     * Create a new timestamp (with time zone) multi-range column on the table.
     */
    public function timestampTzMultiRange(string $column): ColumnDefinition
    {
        return $this->addColumn('tstzmultirange', $column);
    }

    /**
     * Create a new timestamp (with time zone) range column on the table.
     */
    public function timestampTzRange(string $column): ColumnDefinition
    {
        return $this->addColumn('tstzrange', $column);
    }

    /**
     * Create a new tsvector column on the table.
     */
    public function tsvector(string $column): ColumnDefinition
    {
        return $this->addColumn('tsvector', $column);
    }

    /**
     * Create a new universal product number column on the table.
     */
    public function universalProductNumber(string $column): ColumnDefinition
    {
        return $this->addColumn('upc', $column);
    }

    /**
     * Create a new varying bit column on the table.
     */
    public function varbit(string $column, ?int $length = null): ColumnDefinition
    {
        return $this->addColumn('varbit', $column, compact('length'));
    }

    /**
     * Create a new vector column on the table.
     */
    public function vector(string $column, int $dimensions = 1536): ColumnDefinition
    {
        return $this->addColumn('vector', $column, compact('dimensions'));
    }

    /**
     * Create a new xml column on the table.
     */
    public function xml(string $column): ColumnDefinition
    {
        return $this->addColumn('xml', $column);
    }
}
