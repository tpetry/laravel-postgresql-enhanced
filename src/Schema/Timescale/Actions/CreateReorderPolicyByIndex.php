<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions;

use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class CreateReorderPolicyByIndex implements Action
{
    private array $columns;

    /**
     * @param string $columns
     */
    public function __construct(...$columns)
    {
        $this->columns = $columns;
    }

    public function getValue(Grammar $grammar, string $table): array
    {
        $name = str_replace(['-', '.'], '_', strtolower($table.'_'.implode('_', $this->columns).'_index'));

        return ["select add_reorder_policy({$grammar->escape($table)}, {$grammar->escape($name)})"];
    }
}
