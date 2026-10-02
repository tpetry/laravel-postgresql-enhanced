<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\Action;

trait BlueprintTable
{
    /**
     * Set a table to (not) use row level security.
     */
    public function rowLevelSecurity(bool $value = true): Fluent
    {
        return $this->addCommand('rls', compact('value'));
    }

    /**
     * Set timescale hypertable options.
     *
     * @param Action ...$actions
     */
    public function timescale(...$actions): void
    {
        foreach ($actions as $action) {
            $this->addCommand('timescale', ['action' => $action]);
        }
    }

    /**
     * Set a table to be (un)logged.
     */
    public function unlogged(bool $value = true): Fluent
    {
        return $this->addCommand('unlogged', compact('value'));
    }

    /**
     * Set a table's storage options.
     *
     * @param array<string, scalar> $options
     */
    public function with(array $options): Fluent
    {
        return $this->addCommand('storageParameters', compact('options'));
    }
}
