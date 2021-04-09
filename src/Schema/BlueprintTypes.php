<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Schema\ColumnDefinition;

trait BlueprintTypes
{
    /**
     * Create a new big integer range column on the table.
     */
    public function bigIntegerRange(string $column): ColumnDefinition
    {
        return $this->addColumn('bigIntegerRange', $column);
    }

    /**
     * Create a new date range column on the table.
     */
    public function dateRange(string $column): ColumnDefinition
    {
        return $this->addColumn('dateRange', $column);
    }

    /**
     * Create a new decimal range column on the table.
     */
    public function decimalRange(string $column): ColumnDefinition
    {
        return $this->addColumn('decimalRange', $column);
    }

    /**
     * Create a new integer range column on the table.
     */
    public function integerRange(string $column): ColumnDefinition
    {
        return $this->addColumn('integerRange', $column);
    }

    /**
     * Create a new timestamp range column on the table.
     */
    public function timestampRange(string $column): ColumnDefinition
    {
        return $this->addColumn('timestampRange', $column);
    }

    /**
     * Create a new timestamp (with time zone) range column on the table.
     */
    public function timestampTzRange(string $column): ColumnDefinition
    {
        return $this->addColumn('timestampTzRange', $column);
    }
}
