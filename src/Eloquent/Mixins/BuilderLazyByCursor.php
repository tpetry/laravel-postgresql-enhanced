<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Mixins;

use Closure;
use Generator;
use Illuminate\Support\LazyCollection;

/** @mixin \Illuminate\Database\Eloquent\Builder */
class BuilderLazyByCursor
{
    public function lazyByCursor(): Closure
    {
        return function (int $chunkSize = 1000): LazyCollection {
            /* @var \Illuminate\Database\Eloquent\Builder $this */
            return new LazyCollection(function () use ($chunkSize): Generator {
                foreach ($this->applyScopes()->getQuery()->lazyByCursor($chunkSize)->chunk($chunkSize) as $items) {
                    $models = $this->getModel()->hydrate($items->all())->all();
                    if (\count($models) > 0) {
                        $models = $this->eagerLoadRelations($models);
                    }

                    foreach ($models as $model) {
                        yield $model;
                    }
                }
            });
        };
    }
}
