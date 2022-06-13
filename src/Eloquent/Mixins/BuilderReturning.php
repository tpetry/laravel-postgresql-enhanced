<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Mixins;

use Closure;
use Illuminate\Database\Eloquent\Collection;

/** @mixin \Illuminate\Database\Eloquent\Builder */
class BuilderReturning
{
    public function deleteReturning(): Closure
    {
        return function (mixed $id = null, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->deleteReturning($id, $returning)->all()
            );
        };
    }

    public function insertOrIgnoreReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->insertOrIgnoreReturning($values, $returning)->all()
            );
        };
    }

    public function insertReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->insertReturning($values, $returning)->all()
            );
        };
    }

    public function insertUsingReturning(): Closure
    {
        return function (array $columns, $query, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->insertUsingReturning($columns, $query, $returning)->all()
            );
        };
    }

    public function updateFromReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->updateFromReturning($values, $returning)->all()
            );
        };
    }

    public function updateOrInsertReturning(): Closure
    {
        return function (array $attributes, array $values = [], array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->updateOrInsertReturning($attributes, $values, $returning)->all()
            );
        };
    }

    public function updateReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->updateReturning($values, $returning)->all()
            );
        };
    }

    public function upsertReturning(): Closure
    {
        return function (array $values, array|string $uniqueBy, ?array $update = null, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->upsertReturning($values, $uniqueBy, $update, $returning)->all()
            );
        };
    }
}
