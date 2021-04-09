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
     * Create the column definition for an integer range type.
     */
    protected function typeIntegerRange(Fluent $column): string
    {
        return 'int4range';
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
}
