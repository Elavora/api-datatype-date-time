# api-datatype-date-time

[![Packagist Version](https://img.shields.io/packagist/v/elavora/api-datatype-date-time.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-date-time)
[![PHP Version](https://img.shields.io/packagist/php-v/elavora/api-datatype-date-time.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-date-time)
[![Composer Quality](https://github.com/Elavora/api-datatype-date-time/actions/workflows/quality.yml/badge.svg?branch=main)](https://github.com/Elavora/api-datatype-date-time/actions/workflows/quality.yml)
[![CodeQL](https://github.com/Elavora/api-datatype-date-time/actions/workflows/codeql.yml/badge.svg?branch=main)](https://github.com/Elavora/api-datatype-date-time/actions/workflows/codeql.yml)
[![License](https://img.shields.io/packagist/l/elavora/api-datatype-date-time.svg?style=flat-square)](https://packagist.org/packages/elavora/api-datatype-date-time)

DataType imutavel para validar datas e horas e normaliza-las como instantes UTC.

## Requisitos

- PHP 8.3 ou superior.
- Demais requisitos declarados em [`composer.json`](composer.json).

## Instalacao

```bash
composer require elavora/api-datatype-date-time
```

## Inicio rapido

```php
use Elavora\Api\DataTypes\DateTime;

$valor = DateTime::from('2026-05-27T10:30:00-03:00');
$normalizado = $valor->value();
```

`$normalizado` contem `2026-05-27T13:30:00.000000Z`. Entradas sem offset sao interpretadas em UTC.

## Documentacao

Consulte o [guia de uso](docs/USO.md) para parsing, normalizacao e validacao local.
