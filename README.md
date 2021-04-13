# Laravel PostgreSQL Enhanced

![GitHub License](https://img.shields.io/github/license/tpetry/laravel-postgresql-enhanced?label=License)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/tpetry/laravel-postgresql-enhanced.svg?label=Packagist)](https://packagist.org/packages/tpetry/laravel-postgresql-enhanced)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHPUnit?label=Tests)](https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpunit.yml?query=branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style)](https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/php_cs_fixer.yml?query=branch%3Amaster)

Laravel supports many different databases and therefore has to limit itself to the lowest common denominator of all databases. PostgreSQL, however, offers a ton more functionality which is being added to Laravel by this extension.

## Installation

You can install the package via composer:

```bash
composer require tpetry/laravel-postgresql-enhanced
```

## Features

- [Migration](#migration)
  - [Column Types](#column-types)
    - [Bit Strings](#bit-strings)
    - [Case Insensitive Text](#case-insensitive-text)
    - [Hstore](#hstore)
    - [IP Networks](#ip-networks)
    - [International Product Numbers](#international-product-numbers)
    - [Label Tree](#label-tree)
    - [Ranges](#ranges)
    - [XML](#xml)

### Migration

#### Column Types

##### Bit Strings
The bit string data types store strings of 0s and 1s. They can be used to e.g. store bitmaps.
```php
// @see https://www.postgresql.org/docs/current/datatype-bit.html
$table->bit(string $column, int $length = 1);
$table->varbit(string $column, ?int $length = null);
```

##### Case Insensitive Text
The case insensitive text type is used to store a text that will be compared case insensitive. It can be used to e.g. store and compare e-mail addresses.
```php
// @see https://www.postgresql.org/docs/current/citext.html
$table->caseInsensitiveText(string $column);
```

##### IP Networks
The ip network datatype stores an ip network in cidr notation.
```php
// @see https://www.postgresql.org/docs/current/datatype-net-types.html
$table->ipNetwork(string $column);
```

##### Hstore
The hstore data type is used store key/value pairs within a single PostgreSQL value. The new json data type is better in all aspects, so hstore should only be used for compatibility with old applications.
```php
// @see https://www.postgresql.org/docs/current/hstore.html
$table->hstore(string $column);
```

##### International Product Numbers
The international product number data types are used to store common product numbers types and validate them before saving.
```php
// @see https://www.postgresql.org/docs/current/isn.html
$table->europeanArticleNumber13(string $column);
$table->internationalStandardBookNumber(string $column);
$table->internationalStandardBookNumber13(string $column);
$table->internationalStandardMusicNumber(string $column);
$table->internationalStandardMusicNumber13(string $column);
$table->internationalStandardSerialNumber(string $column);
$table->internationalStandardSerialNumber13(string $column);
$table->universalProductNumber(string $column);
```

##### Label Tree
The ltree data type stores a label as its position in a tree. This provides an easy way to manage a tree without performance and complexity disadvantages compared to alternative solutions.
```php
// @see https://www.postgresql.org/docs/current/ltree.html
$table->labelTree(string $column);
```

##### Ranges
The range data types store a range of values with optional start and end values. They can be used e.g. to describe the duration a meeting room is booked.
```php
// @see https://www.postgresql.org/docs/current/rangetypes.html
$table->bigIntegerRange(string $column);
$table->dateRange(string $column);
$table->decimalRange(string $column);
$table->integerRange(string $column);
$table->timestampRange(string $column);
$table->timestampTzRange(string $column);
```

##### XML
The xml data type can be used to store an xml document.
```php
// @see https://www.postgresql.org/docs/current/datatype-xml.html
$table->xml(string $column);
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you discover any security related issues, please email github@tpetry.me instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
