<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use \Illuminate\Contracts\Database\Eloquent\Builder as BaseContract;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;

/**
 * @method PostgresEnhancedConnection getConnection()
 * @method Grammar getGrammar()
 */
class Builder extends BaseBuilder implements BaseContract
{
    use BuilderExplain;
    use BuilderLateralJoin;
    use BuilderLazyByCursor;
    use BuilderReturning;
    use BuilderWhere;
}
