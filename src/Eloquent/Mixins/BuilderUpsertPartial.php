<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Mixins;

use Closure;

/** @mixin \Illuminate\Database\Eloquent\Builder */
class BuilderUpsertPartial
{
    public function upsertPartial(): Closure
    {
        return function (array $values, array|string $uniqueBy, ?array $update, string|callable $where): int {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            if (0 === \count($values)) {
                return 0;
            }

            if (null === $update) {
                $update = array_keys(reset($values));
            }

            return $this->toBase()->upsertPartial(
                $this->addTimestampsToUpsertValues($values),
                $uniqueBy,
                $this->addUpdatedAtToUpsertColumns($update),
                $where
            );
        };
    }
}
