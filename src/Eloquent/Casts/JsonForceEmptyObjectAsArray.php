<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class JsonForceEmptyObjectAsArray implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param ?string $value
     *
     * @return ?array<array-key, mixed>
     */
    public function get($model, string $key, mixed $value, array $attributes): ?array
    {
        if (null === $value) {
            return null;
        }

        return json_decode($value, true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array<array-key, mixed>|\Illuminate\Support\Collection<array-key, mixed>|null $value
     */
    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        if (null === $value) {
            return null;
        }

        return match ($casted = json_encode($value, flags: \JSON_THROW_ON_ERROR)) {
            '[]' => '{}',
            default => $casted,
        };
    }
}
