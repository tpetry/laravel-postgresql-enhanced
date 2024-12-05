<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * The implementations of these functions have been taken from the Laravel core and
 * have been changed in the most minimal way to support the returning clause.
 */
trait BuilderReturning
{
    /**
     * Delete records from the database.
     *
     * @return Collection<int, object>
     */
    public function deleteReturning(mixed $id = null, array $returning = ['*']): Collection
    {
        // If an ID is passed to the method, we will set the where clause to check the
        // ID to let developers to simply and quickly remove a single row from this
        // database without manually specifying the "where" clauses on the query.
        if (null !== $id) {
            $this->where($this->from.'.id', '=', $id);
        }

        $sqlDelete = $this->getGrammar()->compileDelete($this);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        return collect(
            $this->getConnection()->returningStatement("{$sqlDelete} {$sqlReturning}", $this->cleanBindings(
                $this->getGrammar()->prepareBindingsForDelete($this->bindings),
            )),
        );
    }

    /**
     * Insert new records into the database while ignoring errors.
     *
     * @return Collection<int, object>
     */
    public function insertOrIgnoreReturning(array $values, array $returning = ['*']): Collection
    {
        if (empty($values)) {
            return collect();
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $sqlInsert = $this->getGrammar()->compileInsertOrIgnore($this, $values);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);
        $bindings = [...$this->bindings['expressions'], ...Arr::flatten($values, 1)];

        return collect(
            $this->getConnection()->returningStatement(
                "{$sqlInsert} {$sqlReturning}",
                $this->cleanBindings($bindings),
            ),
        );
    }

    /**
     * Insert new records into the database.
     *
     * @return Collection<int, object>
     */
    public function insertReturning(array $values, array $returning = ['*']): Collection
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return collect();
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            // Here, we will sort the insert keys for every record so that each insert is
            // in the same order for the record. We need to make sure this is the case
            // so there are not any errors or problems when inserting these records.
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $sqlInsert = $this->getGrammar()->compileInsert($this, $values);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);
        $bindings = [...$this->bindings['expressions'], ...Arr::flatten($values, 1)];

        // Finally, we will run this query against the database connection and return
        // the results. We will need to also flatten these bindings before running
        // the query so they are all in one huge, flattened array for execution.
        return collect(
            $this->getConnection()->returningStatement(
                "{$sqlInsert} {$sqlReturning}",
                $this->cleanBindings($bindings),
            ),
        );
    }

    /**
     * Insert new records into the table using a subquery.
     *
     * @param \Closure|\Illuminate\Contracts\Database\Query\Builder|string $query
     *
     * @return Collection<int, object>
     */
    public function insertUsingReturning(array $columns, $query, array $returning = ['*']): Collection
    {
        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        [$sql, $bindings] = $this->createSub($query);

        $sqlInsert = $this->getGrammar()->compileInsertUsing($this, $columns, $sql);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);
        $bindings = [...$this->bindings['expressions'], ...$bindings];

        return collect(
            $this->getConnection()->returningStatement(
                "{$sqlInsert} {$sqlReturning}",
                $this->cleanBindings($bindings)
            ),
        );
    }

    /**
     * Update records in a PostgreSQL database using the update from syntax.
     *
     * @return Collection<int, object>
     */
    public function updateFromReturning(array $values, array $returning = ['*']): Collection
    {
        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        return $this->getConnection()->runWithAdditionalBindings(function () use ($returning, $values) {
            $sqlUpdate = $this->getGrammar()->compileUpdateFrom($this, $values);
            $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

            return collect(
                $this->getConnection()->returningStatement("{$sqlUpdate} {$sqlReturning}", $this->cleanBindings(
                    $this->getGrammar()->prepareBindingsForUpdateFrom(['expressions' => []] + $this->bindings, $values)
                )),
            );
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Insert or update a record matching the attributes, and fill it with values.
     *
     * @return Collection<int, object>
     */
    public function updateOrInsertReturning(array $attributes, array $values = [], array $returning = ['*']): Collection
    {
        if (!$this->where($attributes)->exists()) {
            return $this->insertReturning(array_merge($attributes, $values), $returning);
        }

        if (empty($values)) {
            return collect();
        }

        return $this->limit(1)->updateReturning($values, $returning);
    }

    /**
     * Update records in the database.
     *
     * @return Collection<int, object>
     */
    public function updateReturning(array $values, array $returning = ['*']): Collection
    {
        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        return $this->getConnection()->runWithAdditionalBindings(function () use ($returning, $values) {
            $sqlUpdate = $this->getGrammar()->compileUpdate($this, $values);
            $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);

            return collect(
                $this->getConnection()->returningStatement("{$sqlUpdate} {$sqlReturning}", $this->cleanBindings(
                    $this->getGrammar()->prepareBindingsForUpdate(['expressions' => []] + $this->bindings, $values),
                )),
            );
        }, prepend: $this->bindings['expressions']);
    }

    /**
     * Insert new records or update the existing ones.
     *
     * @return Collection<int, object>
     */
    public function upsertReturning(array $values, array|string $uniqueBy, ?array $update = null, array $returning = ['*']): Collection
    {
        if (empty($values)) {
            return collect();
        } elseif ([] === $update) {
            return $this->insertReturning($values, $returning);
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (null === $update) {
            $update = array_keys(reset($values));
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $bindings = $this->cleanBindings(array_merge(
            Arr::flatten($values, 1),
            collect($update)->reject(function ($_value, $key) {
                return \is_int($key);
            })->all()
        ));

        $sqlUpsert = $this->getGrammar()->compileUpsert($this, $values, (array) $uniqueBy, $update);
        $sqlReturning = $this->getGrammar()->compileReturning($this, $returning);
        $bindings = [...$this->bindings['expressions'], ...$bindings];

        return collect(
            $this->getConnection()->returningStatement(
                "{$sqlUpsert} {$sqlReturning}",
                $bindings,
            ),
        );
    }
}
