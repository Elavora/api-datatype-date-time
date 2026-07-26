# Guia de uso

`DateTime` converte entradas validas para UTC no formato `Y-m-d\TH:i:s.u\Z`.

```php
use Elavora\Api\DataTypes\DateTime;

$dateTime = DateTime::from('2026-05-27T10:30:00-03:00');

echo $dateTime->value(); // 2026-05-27T13:30:00.000000Z
```

O offset informado na entrada e respeitado. Strings sem offset sao interpretadas em UTC. Microssegundos sao preservados e datas impossiveis sao rejeitadas.

Tambem sao aceitos objetos que implementem `DateTimeInterface`, alem de expressoes relativas validas como `now`.

```php
$agora = DateTime::now();
$agendamento = DateTime::from('tomorrow 10:00');

$agendamento->assertFuture();
```

`assertFuture()` compara os instantes em UTC, sem depender do timezone global do processo.

## Validacao do pacote

Execute os comandos a partir da raiz do clone:

```bash
docker run --rm -v "${PWD}:/workspace" -w /workspace composer:2 composer update --no-interaction --no-progress --prefer-dist
docker run --rm -v "${PWD}:/workspace" -w /workspace composer:2 composer check
```
