<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

trait GrammarTriggers
{
    /**
     * Compile a drop fulltext index if exists command.
     */
    public function compileCreateTrigger(Blueprint $blueprint, Fluent $command): string
    {
        $name = $command->name;
        $prefix = $command->prefix;
        $table = $command->table;
        $phase = $command->phase;
        $events = $command->events;
        $options = $command->options;
        $for = $options['for'] ?? 'row';
        $condition = $options['condition'] ?? '';
  
        // Determine function details
        $function = is_string($command->function)
          ? [
            'body' => $command->function,
          ]
          : $command->function;
  
        $function['name'] = empty($function['name'])
          ? sprintf('%s%s__%s', $prefix, $table, $name)
          : $function['name'];
        $function['arguments'] = empty($function['arguments'])
          ? []
          : $function['arguments'];
        $function['parameters'] = empty($function['parameters'])
          ? []
          : $function['parameters'];
  
        // Create function that will be used for the trigger
        if ($function['body']) {
          Schema::createFunction(
            name: $function['name'],
            parameters: $function['parameters'],
            language: 'plpgsql',
            return: 'trigger',
            body: $function['body']
          );
        }

        $trigger = [
            'create trigger',
            $this->wrap($command['name']),
            $phase,
            implode(' or ', $command->events),
            'on',
            $this->wrapTable($blueprint),
            'for each ' . $for,
            $condition ? 'WHEN ' . $condition : '',
            'execute function',
            $function['name'] . '(' . implode(',', $this->wrapArray($function['arguments'])) . ')'
        ];
        $sql = implode(' ', array_filter($trigger, fn ($part) => $part));

        return $sql;
    }

    /**
     * Compile a drop trigger command.
     */
    public function compileDropTrigger(Blueprint $blueprint, Fluent $command): string
    {
        return "drop trigger {$this->wrap($command['name'])} on {$this->wrapTable($blueprint)}";
    }

    /**
     * Compile a drop trigger if exists command.
     */
    public function compileDropTriggerIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "drop trigger if exists {$this->wrap($command['name'])} on {$this->wrapTable($blueprint)}";
    }
}
