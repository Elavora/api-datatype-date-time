# Guia de uso

DataType de data e hora para aplicacoes Elavora API.

## Instalacao

```bash
composer require elavora/api-datatype-date-time
```

## Quando usar

- Validar e normalizar valores antes de chegar na regra de negocio.
- Evitar passar strings soltas entre services, DTOs e persistencia.
- Reutilizar a mesma validacao em controllers, comandos e testes.

## Exemplo rapido

```php
use Elavora\Api\DataTypes\DateTime;

$valor = new DateTime('exemplo');
$normalizado = $valor->value();
```

## Principais pontos de entrada

- `Elavora\Api\DataTypes\DateTime`

## Dependencias de runtime

- `elavora/api-datatype-core` `^0.1`

## Validacao no projeto consumidor

Depois de instalar o pacote, rode os testes da aplicacao consumidora. Para uma verificacao isolada do pacote, use container:

```bash
docker run --rm -v "${PWD}:/workspace" -w "/workspace/api-datatype-date-time" composer:2 composer validate --strict --no-check-publish
docker run --rm -v "${PWD}:/workspace" -w "/workspace/api-datatype-date-time" composer:2 sh -lc "find . \\( -path ./.git -o -path ./vendor \\) -prune -o -name '*.php' -print0 | xargs -0 -r -n1 php -l"
```

## Observacoes

- Mantenha regras de produto fora deste pacote.
- Prefira configurar extensoes no bootstrap da aplicacao.
- Instale apenas os modulos que a aplicacao realmente usa.