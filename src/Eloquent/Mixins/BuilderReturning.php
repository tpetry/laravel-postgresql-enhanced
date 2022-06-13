<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Mixins;

use Closure;
use Illuminate\Support\Collection;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class BuilderReturning
{
    public function deleteReturning(): Closure
    {
        return function (mixed $id = null, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->deleteReturning($id, $returning)->all()
                )->all()
            );
        };
    }

    public function insertOrIgnoreReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->insertOrIgnoreReturning($values, $returning)->all()
                )->all()
            );
        };
    }

    public function insertReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->insertReturning($values, $returning)->all()
                )->all()
            );
        };
    }

    public function insertUsingReturning(): Closure
    {
        return function (array $columns, $query, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->insertUsingReturning($columns, $query, $returning)->all()
                )->all()
            );
        };
    }

    public function updateFromReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->updateFromReturning($values, $returning)->all()
                )->all()
            );
        };
    }

    public function updateOrInsertReturning(): Closure
    {
        return function (array $attributes, array $values = [], array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->updateOrInsertReturning($attributes, $values, $returning)->all()
                )->all()
            );
        };
    }

    public function updateReturning(): Closure
    {
        return function (array $values, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->updateReturning($values, $returning)->all()
                )->all()
            );
        };
    }

    public function upsertReturning(): Closure
    {
        return function (array $values, array|string $uniqueBy, ?array $update = null, array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $builder = $this->applyScopes();

            return $builder->getModel()->newCollection(
                $this->getModel()->hydrate(
                    $builder->getQuery()->upsertReturning($values, $uniqueBy, $update, $returning)->all()
                )->all()
            );
        };
    }
}
