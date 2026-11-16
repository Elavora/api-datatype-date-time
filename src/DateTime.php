<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes;

use DateTimeImmutable;
use DateTimeInterface;
use Elavora\Api\Framework\Contracts\DescribesType;
use Exception;
use InvalidArgumentException;

final readonly class DateTime extends AbstractDataType implements DescribesType
{
    /**
     * Descreve o tipo para a documentacao publica (OPTIONS).
     *
     * @return array<string, mixed>
     */
    public static function documentedType(): array
    {
        return [
            'type' => 'string',
            'format' => 'date-time',
            'mask' => '0000-00-00 00:00:00',
            'example' => '2026-05-27 10:30:00',
            'description' => 'Data e hora; value() devolve sempre no formato Y-m-d H:i:s.',
        ];
    }

    /**
     * Cria um DateTime com o instante atual.
     */
    public static function now(): self
    {
        return new self('now');
    }

    /**
     * Verifica se o valor pode ser convertido para data e hora.
     */
    public static function isValid(mixed $value): bool
    {
        if ($value instanceof DateTimeInterface) {
            return true;
        }

        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        try {
            new DateTimeImmutable($value);
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * Garante que a data e hora representada esta no futuro.
     */
    public function assertFuture(): void
    {
        if (new DateTimeImmutable((string) $this->value()) <= new DateTimeImmutable()) {
            throw new InvalidArgumentException('Data e hora devem estar no futuro.');
        }
    }

    protected static function normalize(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (new DateTimeImmutable((string) $value))->format('Y-m-d H:i:s');
    }
}
