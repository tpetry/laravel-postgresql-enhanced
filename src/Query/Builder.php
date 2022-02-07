<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;

/**
 * @method PostgresEnhancedConnection getConnection()
 * @method Grammar getGrammar()
 */
class Builder extends BaseBuilder
{
    use BuilderExplain;
    use BuilderLateralJoin;
    use BuilderReturning;
}
