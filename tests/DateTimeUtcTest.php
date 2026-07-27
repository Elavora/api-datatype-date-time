<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\DateTime\Tests;

use DateTimeImmutable;
use DateTimeZone;
use Elavora\Api\DataTypes\DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DateTimeUtcTest extends TestCase
{
    #[DataProvider('invalidDates')]
    public function testRejectsDatesNormalizedByParserWarnings(string $value): void
    {
        self::assertFalse(DateTime::isValid($value));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidDates(): iterable
    {
        yield 'impossible day' => ['2026-02-30 10:00:00'];
        yield 'impossible hour' => ['2026-05-27 25:00:00'];
    }

    public function testAcceptsLeapDay(): void
    {
        self::assertSame(
            '2024-02-29T00:00:00.000000Z',
            DateTime::from('2024-02-29')->value()
        );
    }

    public function testNormalizesEquivalentOffsetsToSameUtcInstant(): void
    {
        self::assertSame(
            DateTime::from('2026-05-27T10:30:00-03:00')->value(),
            DateTime::from('2026-05-27T13:30:00Z')->value()
        );
    }

    public function testPreservesMicroseconds(): void
    {
        self::assertSame(
            '2026-05-27T13:30:00.123456Z',
            DateTime::from('2026-05-27T10:30:00.123456-03:00')->value()
        );
    }

    public function testNormalizesDateTimeInterfaceToUtc(): void
    {
        $input = new DateTimeImmutable(
            '2026-05-27 10:30:00.654321',
            new DateTimeZone('America/Sao_Paulo')
        );

        self::assertSame(
            '2026-05-27T13:30:00.654321Z',
            DateTime::from($input)->value()
        );
    }

    public function testIgnoresGlobalTimezoneForInputsWithoutOffsetAndFutureCheck(): void
    {
        $originalTimezone = date_default_timezone_get();

        try {
            date_default_timezone_set('America/Sao_Paulo');
            $value = DateTime::from('2026-05-27 10:30:00');

            date_default_timezone_set('Pacific/Auckland');
            self::assertSame('2026-05-27T10:30:00.000000Z', $value->value());

            DateTime::from('2999-01-01 00:00:00')->assertFuture();
        } finally {
            date_default_timezone_set($originalTimezone);
        }
    }

    public function testRejectsPastInstantRegardlessOfGlobalTimezone(): void
    {
        $originalTimezone = date_default_timezone_get();

        try {
            date_default_timezone_set('Pacific/Auckland');
            $this->expectException(InvalidArgumentException::class);

            DateTime::from('2000-01-01T00:00:00Z')->assertFuture();
        } finally {
            date_default_timezone_set($originalTimezone);
        }
    }

    public function testNowRemainsValid(): void
    {
        self::assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            (string) DateTime::now()->value()
        );
    }
}
