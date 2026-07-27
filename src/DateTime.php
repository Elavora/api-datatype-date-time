<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

final readonly class DateTime extends AbstractDataType
{
    /**
     * Cria um DateTime com o instante atual.
     */
    public static function now(): self
    {
        return self::from('now');
    }

    /**
     * Verifica se o valor pode ser convertido para data e hora.
     */
    public static function isValid(mixed $value): bool
    {
        return self::parse($value) !== null;
    }

    /**
     * Garante que a data e hora representada esta no futuro.
     */
    public function assertFuture(): void
    {
        $instant = self::parse($this->value());
        $now = new DateTimeImmutable('now', self::utc());

        if ($instant === null || $instant <= $now) {
            throw new InvalidArgumentException('Data e hora devem estar no futuro.');
        }
    }

    protected static function normalize(mixed $value): string
    {
        $dateTime = self::parse($value);
        if ($dateTime === null) {
            throw new InvalidArgumentException('Data e hora invalidas.');
        }

        return $dateTime
            ->setTimezone(self::utc())
            ->format('Y-m-d\TH:i:s.u\Z');
    }

    private static function parse(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $dateTime = new DateTimeImmutable(trim($value), self::utc());
        } catch (Exception) {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();
        if (
            $errors !== false
            && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)
        ) {
            return null;
        }

        return $dateTime;
    }

    private static function utc(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }
}
