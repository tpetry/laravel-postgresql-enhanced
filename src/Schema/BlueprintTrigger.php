<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;

trait BlueprintTrigger
{
    /**
     * Indicate that the given trigger should be dropped.
     */
    public function dropTrigger(string $name): Fluent
    {
        return $this->addCommand('dropTrigger', ['trigger' => $name]);
    }

    /**
     * Indicate that the given trigger should be dropped if it exists.
     */
    public function dropTriggerIfExists(string $name): Fluent
    {
        return $this->addCommand('dropTriggerIfExists', ['trigger' => $name]);
    }

    /**
     * Create a new trigger on the table.
     */
    public function trigger(string $name, string $action, string $fire): TriggerDefinition
    {
        $trigger = new TriggerDefinition(['name' => 'trigger', 'trigger' => $name] + compact('action', 'fire'));
        $this->commands[] = $trigger;

        return $trigger;
    }
}
