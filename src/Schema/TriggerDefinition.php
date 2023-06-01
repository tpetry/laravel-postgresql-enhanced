<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;

/**
 * @method $this when(string|callable $condition) Specify a condition when the trigger should be called.
 * @method $this replace(bool $value = true) Specify whether the trigger should replace an existing one.
 */
class TriggerDefinition extends Fluent
{
    /**
     * Specify that the trigger should be called for each row.
     */
    public function forEachRow(): static
    {
        $this['forEach'] = 'row';

        return $this;
    }

    /**
     * Specify that the trigger should be called for each statement.
     */
    public function forEachStatement(): static
    {
        $this['forEach'] = 'statement';

        return $this;
    }

    /**
     * Specify that the trigger should be called for each statement.
     */
    public function transitionTables(?string $old = null, ?string $new = null): static
    {
        $this['transitionTables'] = compact('old', 'new');

        return $this;
    }
}
