<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelHelper
{
    public function getCreatedAtColumn(Model $model): ?string
    {
        return match ($model->usesTimestamps()) {
            true => $model->getCreatedAtColumn(),
            false => null,
        };
    }

    public function getDeletedAtColumn(Model $model): ?string
    {
        if (!\in_array(SoftDeletes::class, trait_uses_recursive($model::class)) || !method_exists($model, 'isForceDeleting') || !method_exists($model, 'getDeletedAtColumn')) {
            return null;
        }
        if ($model->isForceDeleting()) {
            return null;
        }

        return $model->getDeletedAtColumn();
    }

    public function getUpdatedAtColumn(Model $model): ?string
    {
        return match ($model->usesTimestamps()) {
            true => $model->getUpdatedAtColumn(),
            false => null,
        };
    }
}
