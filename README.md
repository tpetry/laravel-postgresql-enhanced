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
// Bit String Types
// @see https://www.postgresql.org/docs/current/datatype-bit.html
$table->bit(string $column, int $length = 1);
$table->varbit(string $column, ?int $length = null);

// Case Insensitive Text Type
// @see https://www.postgresql.org/docs/current/citext.html
$table->caseInsensitiveText(string $column);

// International Product Number Types
// @see https://www.postgresql.org/docs/current/isn.html
$table->europeanArticleNumber13(string $column);
$table->internationalStandardBookNumber(string $column);
$table->internationalStandardBookNumber13(string $column);
$table->internationalStandardMusicNumber(string $column);
$table->internationalStandardMusicNumber13(string $column);
$table->internationalStandardSerialNumber(string $column);
$table->internationalStandardSerialNumber13(string $column);
$table->universalProductNumber(string $column);

// Label Tree Type
// @see https://www.postgresql.org/docs/current/ltree.html
$table->labelTree(string $column);

// Network Address Types
// @see https://www.postgresql.org/docs/current/datatype-net-types.html
$table->ipNetwork(string $column);

// Range Types
// @see https://www.postgresql.org/docs/current/rangetypes.html
$table->bigIntegerRange(string $column);
$table->dateRange(string $column);
$table->decimalRange(string $column);
$table->integerRange(string $column);
$table->timestampRange(string $column);
$table->timestampTzRange(string $column);

// XML Type
// @see https://www.postgresql.org/docs/current/datatype-xml.html
$table->xml(string $column);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you discover any security related issues, please email github@tpetry.me instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
