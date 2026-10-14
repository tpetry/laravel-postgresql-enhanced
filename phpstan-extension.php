<?php

declare(strict_types=1);

// Extensions which are only registered when the used PHPStan version supports them.
$services = [];

// IgnoreErrorExtension is only available in PHPStan 2.1.7 and above.
if (interface_exists(PHPStan\Analyser\IgnoreErrorExtension::class)) {
    $services[] = [
        'class' => Tpetry\PostgresqlEnhanced\Support\Phpstan\QueryBuilderOrderByNullsExtension::class,
        'tags' => ['phpstan.ignoreErrorExtension'],
    ];
}

return ['services' => $services];
