# Guia de uso

DataType de data e hora para aplicações Elavora API.

## Instalação

```bash
composer require elavora/api-datatype-date-time
```

## Quando usar

- Validar e normalizar valores antes de chegar na regra de negócio.
- Evitar passar strings soltas entre services, DTOs e persistência.
- Reutilizar a mesma validação em controllers, comandos e testes.

## Exemplo rápido

```php
use Elavora\Api\DataTypes\DateTime;

$data = DateTime::from('2026-05-27T10:30:00');

$data->value();                                  // '2026-05-27 10:30:00'
(string) $data;                                  // '2026-05-27 10:30:00'
json_encode($data);                              // '"2026-05-27 10:30:00"'
DateTime::isValid('2026-05-27 10:30:00');        // true
```

O construtor não é público: crie sempre por `DateTime::from()` (ou `DateTime::now()`).

## Versão e compatibilidade

| Item | Valor |
|---|---|
| Última versão antes do upgrade | `0.2.0` (tag legada `v0.2.0`) |
| Versão do upgrade | `1.0.0` |
| PHP | `>=8.3` |
| `elavora/api-datatype-core` | `^2.0` |

As versões seguintes saem do fluxo central de release: veja [Versões e release](AUTOMACAO.md#versões-e-release).
A versão `1.0.0` atualiza o pacote para o `elavora/api-datatype-core` `^2.0` (que traz o framework 2) e exige PHP 8.3
ou superior.

**Quebra de compatibilidade:** no `elavora/api-datatype-core` 2 o construtor de `AbstractDataType` é
`final protected`, então `new DateTime('2026-05-27 10:30:00')` deixa de funcionar fora da própria classe. Troque por
`DateTime::from('2026-05-27 10:30:00')`. `DateTime::isValid()`, `DateTime::now()`, `assertFuture()` e `value()`
continuam iguais.

### `composer.json`

O `composer.json` do pacote deve ter:

- `require.php` = `">=8.3"` e `require.elavora/api-datatype-core` = `"^2.0"`
- `extra.branch-alias.dev-main` = `"1.0-dev"`
- `autoload.psr-4` = `"Elavora\\Api\\DataTypes\\": "src/"` (sem mudança)
- `require-dev.phpunit/phpunit` = `"^10.5 || ^11.0 || ^12.0"` (sem mudança)
- `scripts.test` = `"phpunit -c phpunit.xml"` (sem mudança)
- `require-dev`, `scripts` e `config` de qualidade (`elavora/code-standards`, PHPStan, `lint`, `analyse`, `check`):
  veja [`composer.json` em Automação](AUTOMACAO.md#composerjson)
- nenhum `repositories` (nem `path`, nem `vcs`)
- `composer validate --strict --no-check-publish` sem erros.

## Criação e entrada inválida

`Elavora\Api\DataTypes\DateTime::from(mixed $value): static` valida o valor com `isValid()` e devolve um `DateTime`
com o valor normalizado (texto no formato `Y-m-d H:i:s`). Se o valor for inválido, lança `\InvalidArgumentException`
com a mensagem `Valor invalido para Elavora\Api\DataTypes\DateTime.`

```php
use Elavora\Api\DataTypes\DateTime;

try {
    DateTime::from('not a date');
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // Valor invalido para Elavora\Api\DataTypes\DateTime.
}
```

Métodos da classe e da instância:

- `DateTime::now(): self`: cria um `DateTime` com o instante atual (equivale a `DateTime::from('now')`).
- `DateTime::isValid(mixed $value): bool`: veja a próxima seção. Não lança exceção.
- `assertFuture(): void`: não retorna nada quando a data e hora estão no futuro. Quando estão no presente ou no passado,
  lança `\InvalidArgumentException` com a mensagem `Data e hora devem estar no futuro.`
- `value(): string|int|bool|float|null` (aqui sempre `string`), `jsonSerialize()` (mesmo valor) e `__toString()`.

```php
use Elavora\Api\DataTypes\DateTime;

DateTime::from('2999-01-01 00:00:00')->assertFuture(); // sem exceção

DateTime::from('2000-01-01 00:00:00')->assertFuture(); // InvalidArgumentException: Data e hora devem estar no futuro.
```

## Validação e normalização

`Elavora\Api\DataTypes\DateTime::isValid(mixed $value): bool` aceita dois tipos de entrada:

- Qualquer objeto `\DateTimeInterface` (`\DateTime`, `\DateTimeImmutable`): sempre válido.
- Uma `string` que não seja vazia nem só espaços e que o PHP consiga interpretar como data e hora (o mesmo
  interpretador do `new \DateTimeImmutable($texto)`, que aceita formatos como `2026-05-27`, `2026-05-27T10:30:00`
  e expressões como `now`).

Qualquer outro tipo (`int`, `float`, `bool`, `null`, array, objeto que não seja `\DateTimeInterface`) não é válido.
Em particular, um timestamp em `int` (`1780000000`) não é aceito: converta antes com `\DateTimeImmutable`.

A normalização guarda sempre uma `string` no formato `Y-m-d H:i:s`: ano com 4 dígitos, mês, dia, hora (00-23),
minuto e segundo com 2 dígitos. Frações de segundo e o fuso horário não são guardados.

| Entrada | `isValid` | `from()` / valor normalizado |
|---|---|---|
| `'2026-05-27 10:30:00'` | `true` | `2026-05-27 10:30:00` |
| `'2026-05-27T10:30:00'` | `true` | `2026-05-27 10:30:00` |
| `'2026-05-27'` | `true` | `2026-05-27 00:00:00` (sem hora, vale meia-noite) |
| `'2026-05-27 10:30:00.123456'` | `true` | `2026-05-27 10:30:00` (fração descartada) |
| `'2026-05-27T10:30:00+00:00'` | `true` | `2026-05-27 10:30:00` (fuso descartado, vale o horário escrito) |
| `new \DateTimeImmutable('2026-05-27 10:30:00')` | `true` | `2026-05-27 10:30:00` |
| `new \DateTime('2026-05-27 10:30:00')` | `true` | `2026-05-27 10:30:00` |
| `'now'` | `true` | o instante atual, em `Y-m-d H:i:s` |
| `'not a date'` | `false` | lança `InvalidArgumentException` |
| `''` | `false` | lança `InvalidArgumentException` |
| `'   '` (só espaços) | `false` | lança `InvalidArgumentException` |
| `null`, `false`, `true` | `false` | lança `InvalidArgumentException` |
| `1780000000` (`int`) | `false` | lança `InvalidArgumentException` |
| `[]` (array) | `false` | lança `InvalidArgumentException` |

Detalhes que a tabela mostra:

- Para uma entrada `\DateTimeInterface`, o resultado é o horário local do próprio objeto (no fuso dele). Para uma
  `string`, o fuso padrão do PHP (`date_default_timezone_get()`) é usado quando o texto não traz fuso.
- Textos relativos como `'now'` ou `'tomorrow'` dependem do momento da chamada: não use em comparações fixas.
- O valor guardado é sempre `string`, e não um objeto de data. Para voltar a um objeto, use
  `new \DateTimeImmutable($data->value())`.

Contraexemplo (comportamento que não vale mais): `new DateTime('2026-05-27 10:30:00')` funcionava no
`elavora/api-datatype-core` `^0.1` e falha no `^2.0`, porque o construtor é `final protected`.

## Principais pontos de entrada

- `Elavora\Api\DataTypes\DateTime` (`final readonly`, herda de `Elavora\Api\DataTypes\AbstractDataType`)

## Dependências de runtime

- `elavora/api-datatype-core` `^2.0`

## Validação do pacote

O passo a passo para rodar lint, PHPStan e testes em container está em [Desenvolvimento e testes](DESENVOLVIMENTO.md).
Na aplicação consumidora, rode os testes dela depois de instalar o pacote.

## Observações

- Mantenha regras de produto fora deste pacote.
- Prefira configurar extensões no bootstrap da aplicação.
- Instale apenas os módulos que a aplicação realmente usa.
