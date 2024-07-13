<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
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
        $sql = [];

        foreach (array_reverse($blueprint->getAddedColumns()) as $column) {
            $attributes = $column->getAttributes();
            if (!\array_key_exists('initial', $attributes)) {
                continue;
            }

            if (\array_key_exists('default', $attributes)) {
                $sql[] = sprintf('alter table %s alter column %s set default %s',
                    $this->wrapTable($blueprint),
                    $this->wrap($column),
                    $this->getDefaultValue($column['default'])
                );
            } else {
                $sql[] = sprintf('alter table %s alter column %s drop default',
                    $this->wrapTable($blueprint),
                    $this->wrap($column),
                );
            }

            $column['default'] = $column['initial'];
        }

        $sql[] = sprintf('alter table %s %s',
            $this->wrapTable($blueprint),
            implode(', ', $this->prefixArray('add column', $this->getColumns($blueprint)))
        );

        $sql[] = sprintf('alter table %s add column %s',
            $this->wrapTable($blueprint),
            $this->getColumn($blueprint, $command->column)
        );

        return array_reverse($sql);
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
