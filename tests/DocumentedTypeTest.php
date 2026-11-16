<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\DateTime\Tests;

use Elavora\Api\DataTypes\DateTime;
use Elavora\Api\Framework\Contracts\DescribesType;
use PHPUnit\Framework\TestCase;

/**
 * @see docs/USO.md#documentação-do-tipo-options
 */
final class DocumentedTypeTest extends TestCase
{
    private const ALLOWED_KEYS = ['type', 'format', 'mask', 'pattern', 'enum', 'example', 'description'];

    public function testImplementsDescribesType(): void
    {
        self::assertInstanceOf(DescribesType::class, new DateTime('2026-05-27 10:30:00'));
    }

    public function testDocumentedTypeUsesOnlyAllowedKeys(): void
    {
        $documented = DateTime::documentedType();

        self::assertArrayHasKey('type', $documented);
        self::assertSame('string', $documented['type']);
        self::assertSame('date-time', $documented['format']);
        self::assertSame([], array_diff(array_keys($documented), self::ALLOWED_KEYS));
    }

    public function testExampleIsValidAndKeepsCanonicalFormat(): void
    {
        $documented = DateTime::documentedType();

        self::assertArrayHasKey('example', $documented);
        self::assertTrue(DateTime::isValid($documented['example']));
        self::assertSame($documented['example'], (new DateTime($documented['example']))->value());
    }

    public function testMaskMatchesExample(): void
    {
        $documented = DateTime::documentedType();

        $regex = '~^' . str_replace('0', '\d', $documented['mask']) . '$~';
        self::assertSame(1, preg_match($regex, (string) $documented['example']));
    }

    public function testPatternMatchesExampleWhenDeclared(): void
    {
        $documented = DateTime::documentedType();
        if (!isset($documented['pattern'])) {
            self::assertArrayNotHasKey('pattern', $documented);

            return;
        }

        self::assertSame(1, preg_match('~' . $documented['pattern'] . '~', (string) $documented['example']));
    }
}
