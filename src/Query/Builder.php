<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use BuilderExplain;
    use BuilderLateralJoin;
}
