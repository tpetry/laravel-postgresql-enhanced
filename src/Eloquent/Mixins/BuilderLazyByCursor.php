<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Mixins;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

/** @mixin \Illuminate\Database\Eloquent\Builder */
class BuilderLazyByCursor
{
    public function lazyByCursor(): Closure
    {
        return function (int $chunkSize = 1000): LazyCollection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->applyScopes()->getQuery()->lazyByCursor($chunkSize)->map(function (object $record): Model {
                return $this->newModelInstance()->newFromBuilder((array) $record);
            });
        };
    }
}
