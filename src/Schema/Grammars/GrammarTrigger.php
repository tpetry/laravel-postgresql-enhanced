<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait GrammarTrigger
{
    /**
     * Compile a drop trigger command.
     */
    public function compileDropTrigger(Blueprint $blueprint, Fluent $command): string
    {
        return "drop trigger {$this->wrap($command['trigger'])} on {$this->wrapTable($blueprint->getTable())}";
    }

    /**
     * Compile a drop trigger if exists command.
     */
    public function compileDropTriggerIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "drop trigger if exists {$this->wrap($command['trigger'])} on {$this->wrapTable($blueprint->getTable())}";
    }

    /**
     * Compile a create trigger command.
     */
    public function compileTrigger(Blueprint $blueprint, Fluent $command): string
    {
        if (filled($command['transitionTables'])) {
            $new = transform($command['transitionTables']['new'], fn (string $table) => "new table as {$this->wrap($table)}");
            $old = transform($command['transitionTables']['old'], fn (string $table) => "old table as {$this->wrap($table)}");
            if (filled($new) || filled($old)) {
                $referencing = implode(' ', array_filter(['referencing', $new, $old], fn ($part) => $part));
            }
        }

        $forEach = match ($command['forEach']) {
            'row' => 'for each row',
            'statement' => 'for each statement',
            default => null,
        };

        if (filled($command['when'])) {
            $condition = $command['when'];
            if ($condition instanceof Closure) {
                $query = ($condition)(DB::query());
                $condition = trim(str_replace('select * where', '', Query::toSql($query)));
                $condition = str_replace(['"NEW"', '"OLD"'], ['NEW', 'OLD'], $condition); // NEW/OLD have to be unescaped!
            }
            $when = "when ({$condition})";
        }

        $index = [
            $command['replace'] ? 'create or replace trigger' : 'create trigger',
            $this->wrap($command['trigger']),
            $command['fire'],
            'on',
            $this->wrap($blueprint->getTable()),
            $referencing ?? null,
            $forEach,
            $when ?? null,
            "execute function {$command['action']}",
        ];
        $sql = implode(' ', array_filter($index, fn ($part) => $part));

        return $sql;
    }
}
