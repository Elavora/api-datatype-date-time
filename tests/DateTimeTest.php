<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\DateTime\Tests;

use Elavora\Api\DataTypes\DateTime;
use PHPUnit\Framework\TestCase;

final class DateTimeTest extends TestCase
{
    public function testValidatesDateTime(): void
    {
        self::assertSame(
            '2026-05-27T10:30:00.000000Z',
            DateTime::from('2026-05-27 10:30:00')->value()
        );
        self::assertFalse(DateTime::isValid('not a date'));
    }
}
