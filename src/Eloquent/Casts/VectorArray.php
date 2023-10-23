<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class VectorArray implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param ?string $value
     *
     * @return ?array<int, float>
     */
    public function get($model, string $key, mixed $value, array $attributes): ?array
    {
        if (null === $value) {
            return null;
        }

        return json_decode($value, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array<int, float>|\Illuminate\Support\Collection<int, float>|null $value
     */
    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        if (null === $value) {
            return null;
        }

        return json_encode($value, flags: \JSON_THROW_ON_ERROR);
    }
}
