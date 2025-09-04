<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;

/**
 * @method PostgresEnhancedConnection getConnection()
 * @method Grammar getGrammar()
 */
class Builder extends BaseBuilder
{
    use BuilderCte;
    use BuilderExplain;
    use BuilderLateralJoin;
    use BuilderLazyByCursor;
    use BuilderOrder;
    use BuilderReturning;
    use BuilderUpsertPartial;
    use BuilderWhere;

    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = [
        'expressions' => [],
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'unionOrder' => [],
    ];

    /**
     * Insert new records into the database.
     */
    public function insert(array $values): bool
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($values) {
            return parent::insert($values);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Insert a new record and get the value of the primary key.
     */
    public function insertGetId(array $values, $sequence = null): mixed
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($sequence, $values) {
            return parent::insertGetId($values, $sequence);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Insert new records into the database while ignoring errors.
     */
    public function insertOrIgnore(array $values): int
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($values) {
            return parent::insertOrIgnore($values);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Insert new records into the table using a subquery.
     */
    public function insertUsing(array $columns, $query): int
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($columns, $query) {
            return parent::insertUsing($columns, $query);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Update records in the database.
     */
    public function update(array $values): int
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($values) {
            $this->bindings['expressions'] = [];

            return parent::update($values);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Update records in a PostgreSQL database using the update from syntax.
     */
    public function updateFrom(array $values): int
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($values) {
            $this->bindings['expressions'] = [];

            return parent::updateFrom($values);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Insert new records or update the existing ones.
     */
    public function upsert(array $values, $uniqueBy, $update = null): int
    {
        return $this->getConnection()->runWithAdditionalBindings(function () use ($uniqueBy, $update, $values) {
            return parent::upsert($values, $uniqueBy, $update);
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Get a new join clause.
     */
    protected function newJoinClause($parentQuery, $type, $table): JoinClause
    {
        return new JoinClause($parentQuery, $type, $table);
    }
}
