<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;
use Illuminate\Database\Schema\Grammars\Grammar as BaseGrammar;

// In Laravel 12.0.0 the BaseBlueprint constructor was changed so the behaviour of before/after is emulated:
// - Laravel >=12: store($this->connection, $this->grammar, $this->table), execute($callback)
// - Laravel <=11: store($this->table, $this->prefix), execute($callback)
class Blueprint extends BaseBlueprint
{
    use BlueprintIndex;
    use BlueprintTable;
    use BlueprintTrigger;
    use BlueprintTypes;

    protected Connection $connection;
    protected BaseGrammar $grammar;
    protected $prefix;
    protected $table;

    public function __construct(
        Connection $connection,
        string $table,
        ?Closure $callback = null,
    ) {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
        $this->table = $table;
        $this->prefix = $connection->getTablePrefix();

        if (null !== $callback) {
            $callback($this);
        }
    }
}
