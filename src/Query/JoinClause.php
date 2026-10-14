<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\JoinClause as BaseJoinClause;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;

/**
 * @phpstan-import-type Bindings from Builder
 *
 * @method PostgresEnhancedConnection getConnection()
 * @method Grammar getGrammar()
 */
class JoinClause extends BaseJoinClause
{
    use BuilderCte;
    use BuilderExplain;
    use BuilderLateralJoin;
    use BuilderLazyByCursor;
    use BuilderOrder;
    use BuilderReturning;
    use BuilderUpsertPartial;
    use BuilderWhere;

    /**
     * The current query value bindings.
     *
     * @var Bindings
     */
    public $bindings = [
        'expressions' => [],
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'having' => [],
        'order' => [],
        'union' => [],
        'unionOrder' => [],
    ];
}
