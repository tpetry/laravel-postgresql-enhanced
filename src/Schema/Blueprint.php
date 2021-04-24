<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{
    use BlueprintIndex;
    use BlueprintTypes;
}
