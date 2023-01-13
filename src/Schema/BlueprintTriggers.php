<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;

/**
 * @method \Illuminate\Database\Schema\IndexDefinition createTrigger(string $name, string $phase, array $events, string|array $function = '', ?array $options = [])
 * @method \Illuminate\Database\Schema\IndexDefinition dropTrigger(string $name)
 */
trait BlueprintTriggers
{
    /**
     * Create trigger for table
     */
    public function createTrigger(string $name, string $phase, array $events, string|array $function = '', ?array $options = []): Fluent
    {      
        return $this->addCommand('createTrigger', compact('name', 'prefix', 'phase', 'events', 'function', 'options'));
    }

    /**
     * Drop trigger from table.
     */
    public function dropIndexIfExists(string $name): Fluent
    {
        return $this->addCommand('dropTrigger', compact('name'));
    }
}
