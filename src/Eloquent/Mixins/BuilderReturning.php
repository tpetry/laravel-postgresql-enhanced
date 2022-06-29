<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Mixins;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @mixin \Illuminate\Database\Eloquent\Builder */
class BuilderReturning
{
    public function deleteReturning(): Closure
    {
        return function (array $returning = ['*']): Collection {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $model = $this->getModel();

            $usesSoftDeletesTrait = \in_array(SoftDeletes::class, trait_uses_recursive($model::class));
            $hasDeletedAtColumn = method_exists($model, 'getDeletedAtColumn') && filled($model->getDeletedAtColumn());
            if ($usesSoftDeletesTrait && $hasDeletedAtColumn) {
                return $this->updateReturning([
                    $model->getDeletedAtColumn() => $model->freshTimestampString(),
                ], $returning);
            }

            return $this->forceDeleteReturning($returning);
        };
    }

    public function forceDeleteReturning(): Closure
    {
        return function (array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->hydrate(
                $this->applyScopes()->getQuery()->deleteReturning(null, $returning)->all()
            )->each(fn (Model $model) => $model->exists = false);
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
                $this->applyScopes()->getQuery()->updateReturning(
                    $this->addUpdatedAtColumn($values),
                    $returning
                )->all()
            );
        };
    }

    public function upsertReturning(): Closure
    {
        return function (array $values, array|string $uniqueBy, ?array $update = null, array $returning = ['*']): Collection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            if (0 === \count($values)) {
                return new Collection();
            }

            if (null === $update) {
                $update = array_keys(reset($values));
            }

            return $this->hydrate(
                $this->applyScopes()->getQuery()->upsertReturning(
                    $this->addTimestampsToUpsertValues($values),
                    $uniqueBy,
                    $this->addUpdatedAtToUpsertColumns($update),
                    $returning,
                )->all()
            );
        };
    }
}
