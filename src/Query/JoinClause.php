<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\JoinClause as BaseJoinClause;

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
}
