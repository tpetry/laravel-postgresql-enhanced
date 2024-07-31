<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

trait GrammarTable
{
    /**
     * Compile a column addition command.
     *
     * @return array<int, string>
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command): array
    {
        // In Laravel 11.15.0 the logic was changed that compileAdd is only for one column (the one in the command) of
        // the blueprint and not all ones of the blueprint as before.
        /** @var \Illuminate\Database\Schema\ColumnDefinition[] $columns */
        $columns = isset($command['column']) ? [$command['column']] : $blueprint->getAddedColumns();

        $sqlChangeDefault = [];
        foreach ($columns as $column) {
            $attributes = $column->getAttributes();
            if (!\array_key_exists('initial', $attributes)) {
                continue;
            }

            // Transform the column definition to a standard one understood by Laravel:
            // - The `initial` modifier is saved to the `default` modifier to set the initial value when creating the column.
            // - A SQL query is created to reset the `default` value afterward to NULL or the specified value.
            $sqlChangeDefault[] = match (\array_key_exists('default', $attributes)) {
                true => "alter table {$this->wrapTable($blueprint)} alter column {$this->wrap($column)} set default {$this->getDefaultValue($column['default'])}",
                false => "alter table {$this->wrapTable($blueprint)} alter column {$this->wrap($column)} drop default",
            };
            $column['default'] = $column['initial'];
        }

        return [
            ...Arr::wrap(parent::compileAdd($blueprint, $command)), // Some Laravel versions produce a single string while others an array.
            ...$sqlChangeDefault,
        ];
    }

    /**
     * Compile a table storage parameters command.
     */
    public function compileStorageParameters(Blueprint $blueprint, Fluent $command): string
    {
        $options = $command->get('options');
        $options = array_map(fn (string $value, string $key) => "{$key} = {$value}", $options, array_keys($options));
        $storageParameters = implode(', ', $options);

        return "alter table {$this->wrapTable($blueprint->getTable())} set ({$storageParameters})";
    }

    /**
     * Compile a table unlogged command.
     */
    public function compileUnlogged(Blueprint $blueprint, Fluent $command): string
    {
        return match ((bool) $command->get('value')) {
            true => "alter table {$this->wrapTable($blueprint->getTable())} set unlogged",
            false => "alter table {$this->wrapTable($blueprint->getTable())} set logged",
        };
    }
}
