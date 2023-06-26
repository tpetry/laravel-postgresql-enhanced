<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class IntegerArrayCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param ?string $value
     *
     * @return ?array<int, mixed>
     */
    public function get($model, string $key, mixed $value, array $attributes): ?array
    {
        if (null === $value) {
            return null;
        }

        return json_decode(str_replace(['{', '}'], ['[', ']'], $value));
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array<int, mixed>|\Illuminate\Support\Collection<int, mixed>|null $value
     */
    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        if (null === $value) {
            return null;
        }

        return str_replace(['[', ']'], ['{', '}'], json_encode($value));
    }
}
