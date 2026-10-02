<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use RuntimeException;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait GrammarPolicy
{
    public function compileAlterPolicy(Blueprint $blueprint, Fluent $command): string
    {
        [$name, $type] = $this->extractPolicyInformation($command['method'], $command['policy']);

        $condition = $command['condition'];
        if ($condition instanceof Closure) {
            $query = ($condition)(DB::query());
            $condition = trim(str_replace('select * where', '', Query::toSql($query)));
        }
        if ($this->connection->getSchemaGrammar()->isExpression($condition)) {
            $condition = $this->connection->getSchemaGrammar()->getValue($condition);
        }

        return "alter policy {$this->wrap($name)} on {$this->wrapTable($blueprint->getTable())} to {$this->wrap($command['user'])} {$type} ({$condition})";
    }

    public function compileDropPolicy(Blueprint $blueprint, Fluent $command): string
    {
        [$name] = $this->extractPolicyInformation($command['method'], $command['policy']);

        return match ((bool) $command['ifExists']) {
            true => "drop policy if exists {$this->wrap($name)} on {$this->wrapTable($blueprint->getTable())}",
            false => "drop policy {$this->wrap($name)} on {$this->wrapTable($blueprint->getTable())}",
        };
    }

    public function compilePolicy(Blueprint $blueprint, Fluent $command): string
    {
        [$name, $type] = $this->extractPolicyInformation($command['method'], $command['policy']);

        $condition = $command['condition'];
        if ($condition instanceof Closure) {
            $query = ($condition)(DB::query());
            $condition = trim(str_replace('select * where', '', Query::toSql($query)));
        }
        if ($this->connection->getSchemaGrammar()->isExpression($condition)) {
            $condition = $this->connection->getSchemaGrammar()->getValue($condition);
        }

        return "create policy {$this->wrap($name)} on {$this->wrapTable($blueprint->getTable())} for {$command['method']} to {$this->wrap($command['user'])} {$type} ({$condition})";
    }

    /**
     * @return array{string, string}
     */
    private function extractPolicyInformation(string $method, string $name): array
    {
        return match ($method) {
            'delete' => ["{$name}_delete", 'using'],
            'insert' => ["{$name}_insert", 'with check'],
            'select' => ["{$name}_select", 'using'],
            'update' => ["{$name}_update", 'using'],
            'all' => [$name, 'using'],
            default => throw new RuntimeException("Unknown method '{$method}'."),
        };
    }
}
