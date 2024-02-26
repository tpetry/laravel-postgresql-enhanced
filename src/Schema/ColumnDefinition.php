<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Schema\ColumnDefinition as BaseColumnDefinition;

/**
 * @internal This class is not used. It only exists to teach Laravel projects using PHPStan or IDEs supporting auto-suggest about added functionality.
 */
class ColumnDefinition extends BaseColumnDefinition
{
    /**
     * Specify the compression method for TOASTed values (PostgreSQL).
     */
    public function compression(string $algorithm): self
    {
        return $this;
    }

    /**
     * Sets an initial value to the column (PostgreSQL).
     */
    public function initial(mixed $value): self
    {
        return $this;
    }

    /**
     * Specify casting expression when changing the column type (PostgreSQL).
     */
    public function using(string|Expression $expression): self
    {
        return $this;
    }
}
