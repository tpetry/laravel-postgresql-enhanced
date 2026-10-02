<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Closure;
use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Fluent;

trait BlueprintPolicy
{
    /**
     * Changes a row-level security policy for all SQL statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function alterPolicy(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('alterPolicy', ['policy' => $name, 'method' => 'all'] + compact('user', 'condition'));
    }

    /**
     * Changes a row-level security policy for DELETE statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function alterPolicyDelete(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('alterPolicy', ['policy' => $name, 'method' => 'delete'] + compact('user', 'condition'));
    }

    /**
     * Changes a row-level security policy for INSERT statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function alterPolicyInsert(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('alterPolicy', ['policy' => $name, 'method' => 'insert'] + compact('user', 'condition'));
    }

    /**
     * Changes a row-level security policy for SELECT statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function alterPolicySelect(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('alterPolicy', ['policy' => $name, 'method' => 'select'] + compact('user', 'condition'));
    }

    /**
     * Changes a row-level security policy for UPDATE statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function alterPolicyUpdate(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('alterPolicy', ['policy' => $name, 'method' => 'update'] + compact('user', 'condition'));
    }

    /**
     * Create a new row-level security policy for all SQL statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function createPolicy(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('policy', ['policy' => $name, 'method' => 'all'] + compact('user', 'condition'));
    }

    /**
     * Create a new row-level security policy for DELETE statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function createPolicyDelete(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('policy', ['policy' => $name, 'method' => 'delete'] + compact('user', 'condition'));
    }

    /**
     * Create a new row-level security policy for INSERT statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function createPolicyInsert(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('policy', ['policy' => $name, 'method' => 'insert'] + compact('user', 'condition'));
    }

    /**
     * Create a new row-level security policy for SELECT statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function createPolicySelect(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('policy', ['policy' => $name, 'method' => 'select'] + compact('user', 'condition'));
    }

    /**
     * Create a new row-level security policy for UPDATE statements.
     *
     * @param (Closure(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed)|Expression|ExpressionContract|string $condition
     */
    public function createPolicyUpdate(string $name, string $user, Closure|Expression|ExpressionContract|string $condition): Fluent
    {
        return $this->addCommand('policy', ['policy' => $name, 'method' => 'update'] + compact('user', 'condition'));
    }

    public function dropPolicy(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'all', 'ifExists' => false]);
    }

    public function dropPolicyDelete(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'delete', 'ifExists' => false]);
    }

    public function dropPolicyDeleteIfExists(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'delete', 'ifExists' => true]);
    }

    public function dropPolicyIfExists(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'all', 'ifExists' => true]);
    }

    public function dropPolicyInsert(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'insert', 'ifExists' => false]);
    }

    public function dropPolicyInsertIfExists(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'insert', 'ifExists' => true]);
    }

    public function dropPolicySelect(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'select', 'ifExists' => false]);
    }

    public function dropPolicySelectIfExists(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'select', 'ifExists' => true]);
    }

    public function dropPolicyUpdate(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'update', 'ifExists' => false]);
    }

    public function dropPolicyUpdateIfExists(string $name): Fluent
    {
        return $this->addCommand('dropPolicy', ['policy' => $name, 'method' => 'update', 'ifExists' => true]);
    }
}
