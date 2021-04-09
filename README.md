# Laravel PostgreSQL Enhanced

![GitHub License](https://img.shields.io/github/license/tpetry/laravel-postgresql-enhanced?label=License)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/tpetry/laravel-postgresql-enhanced.svg?label=Packagist)](https://packagist.org/packages/tpetry/laravel-postgresql-enhanced)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHPUnit?label=Tests)](https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpunit.yml?query=branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style)](https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/php_cs_fixer.yml?query=branch%3Amaster)

The standard Laravel PostgreSQL driver is extended with many missing PostgreSQL functionalities.
  
## Installation

You can install the package via composer:

```bash
composer require tpetry/laravel-postgresql-enhanced
```

## Table of Contents

### Migration

#### Column Types

```php
// Range Types
$table->bigIntegerRange(string $column);
$table->dateRange(string $column);
$table->decimalRange(string $column);
$table->integerRange(string $column);
$table->timestampRange(string $column);
$table->timestampTzRange(string $column);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you discover any security related issues, please email github@tpetry.me instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
