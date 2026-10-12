<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\Facades\App;
use PDO;
use SensitiveParameter;

class PostgresqlEnhancedConnector extends PostgresConnector
{
    protected function createPdoConnection($dsn, $username, #[SensitiveParameter] $password, $options): PDO
    {
        if (version_compare(\PHP_VERSION, '8.4.0', '<') || version_compare(App::version(), '11.0.0', '<') || !empty($options[PDO::ATTR_PERSISTENT])) {
            return parent::createPdoConnection($dsn, $username, $password, $options);
        }

        return new PostgresqlEnhancedPdoConnection($dsn, $username, $password, $options);
    }
}
