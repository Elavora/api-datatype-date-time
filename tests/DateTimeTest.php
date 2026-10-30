<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\DateTime\Tests;

use Elavora\Api\DataTypes\AbstractDataType;
use Elavora\Api\DataTypes\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @see docs/USO.md#validação-e-normalização
 * @see docs/USO.md#criação-e-entrada-inválida
 */
final class DateTimeTest extends TestCase
{
    private const INVALID_MESSAGE = 'Valor invalido para Elavora\Api\DataTypes\DateTime.';
    private const FUTURE_MESSAGE = 'Data e hora devem estar no futuro.';

    public function testValidatesDateTime(): void
    {
        self::assertSame('2026-05-27 10:30:00', DateTime::from('2026-05-27 10:30:00')->value());
        self::assertFalse(DateTime::isValid('not a date'));
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function validProvider(): array
    {
        return [
            'data e hora com espaco' => ['2026-05-27 10:30:00', '2026-05-27 10:30:00'],
            'formato ISO com T' => ['2026-05-27T10:30:00', '2026-05-27 10:30:00'],
            'so data vale meia-noite' => ['2026-05-27', '2026-05-27 00:00:00'],
            'fracao descartada' => ['2026-05-27 10:30:00.123456', '2026-05-27 10:30:00'],
            'fuso +00:00 descartado' => ['2026-05-27T10:30:00+00:00', '2026-05-27 10:30:00'],
            'fuso -03:00 descartado' => ['2026-05-27T10:30:00-03:00', '2026-05-27 10:30:00'],
            'DateTimeImmutable' => [new \DateTimeImmutable('2026-05-27 10:30:00'), '2026-05-27 10:30:00'],
            'DateTime nativo' => [new \DateTime('2026-05-27 10:30:00'), '2026-05-27 10:30:00'],
            'DateTimeImmutable com fracao' => [
                new \DateTimeImmutable('2026-05-27 10:30:00.987654'),
                '2026-05-27 10:30:00',
            ],
            'DateTimeImmutable em outro fuso usa horario local do objeto' => [
                new \DateTimeImmutable('2026-05-27 10:30:00', new \DateTimeZone('Asia/Tokyo')),
                '2026-05-27 10:30:00',
            ],
            'DateTime em outro fuso usa horario local do objeto' => [
                new \DateTime('2026-05-27 23:15:45', new \DateTimeZone('America/Sao_Paulo')),
                '2026-05-27 23:15:45',
            ],
        ];
    }

    #[DataProvider('validProvider')]
    public function testIsValidAcceptsValue(mixed $input, string $expected): void
    {
        self::assertTrue(DateTime::isValid($input));
    }

    #[DataProvider('validProvider')]
    public function testFromNormalizesToYmdHis(mixed $input, string $expected): void
    {
        $date = DateTime::from($input);

        self::assertSame($expected, $date->value());
        self::assertSame($expected, (string) $date);
        self::assertSame(json_encode($expected), json_encode($date));
        self::assertSame($expected, $date->jsonSerialize());
    }

    public function testValueIsAlwaysStringNotObject(): void
    {
        $date = DateTime::from(new \DateTimeImmutable('2026-05-27 10:30:00'));

        self::assertIsString($date->value());
        self::assertSame(
            '2026-05-27 10:30:00',
            (new \DateTimeImmutable((string) $date->value()))->format('Y-m-d H:i:s'),
        );
    }

    public function testNowIsValidAndNormalizedToCurrentInstant(): void
    {
        self::assertTrue(DateTime::isValid('now'));

        $before = time();
        $fromNow = DateTime::from('now');
        $after = time();

        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $fromNow->value());
        $ts = strtotime((string) $fromNow->value());
        self::assertGreaterThanOrEqual($before, $ts);
        self::assertLessThanOrEqual($after, $ts);
    }

    public function testNowFactoryReturnsCurrentInstant(): void
    {
        $before = time();
        $now = DateTime::now();
        $after = time();

        self::assertInstanceOf(DateTime::class, $now);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $now->value());
        $ts = strtotime((string) $now->value());
        self::assertGreaterThanOrEqual($before, $ts);
        self::assertLessThanOrEqual($after, $ts);
    }

    public function testRelativeTextIsValid(): void
    {
        self::assertTrue(DateTime::isValid('tomorrow'));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidProvider(): array
    {
        return [
            'texto que nao e data' => ['not a date'],
            'string vazia' => [''],
            'so espacos' => ['   '],
            'espacos, tab e quebra de linha' => [" \t\n "],
            'null' => [null],
            'false' => [false],
            'true' => [true],
            'timestamp int' => [1780000000],
            'zero' => [0],
            'float' => [1.5],
            'array vazio' => [[]],
            'array com valores' => [['2026-05-27']],
            'objeto que nao e DateTimeInterface' => [new \stdClass()],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function testIsValidRejectsValueWithoutThrowing(mixed $input): void
    {
        self::assertFalse(DateTime::isValid($input));
    }

    #[DataProvider('invalidProvider')]
    public function testFromRejectsInvalidValue(mixed $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::INVALID_MESSAGE);

        DateTime::from($input);
    }

    public function testFromInvalidMessageIsExact(): void
    {
        try {
            DateTime::from('not a date');
            self::fail('Deveria lancar InvalidArgumentException.');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Valor invalido para Elavora\Api\DataTypes\DateTime.', $e->getMessage());
        }
    }

    public function testAssertFutureAcceptsFarFutureDate(): void
    {
        DateTime::from('2999-01-01 00:00:00')->assertFuture();

        $this->addToAssertionCount(1);
    }

    public function testAssertFutureRejectsPastDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::FUTURE_MESSAGE);

        DateTime::from('2000-01-01 00:00:00')->assertFuture();
    }

    public function testAssertFutureRejectsPresent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(self::FUTURE_MESSAGE);

        DateTime::now()->assertFuture();
    }

    public function testAssertFutureMessageIsExact(): void
    {
        try {
            DateTime::from('2000-01-01 00:00:00')->assertFuture();
            self::fail('Deveria lancar InvalidArgumentException.');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Data e hora devem estar no futuro.', $e->getMessage());
        }
    }

    public function testClassContract(): void
    {
        $class = new \ReflectionClass(DateTime::class);

        self::assertTrue($class->isFinal());
        self::assertTrue($class->isReadOnly());
        self::assertTrue($class->isSubclassOf(AbstractDataType::class));
        self::assertInstanceOf(\JsonSerializable::class, DateTime::from('2026-05-27'));
        self::assertInstanceOf(\Stringable::class, DateTime::from('2026-05-27'));
    }

    public function testConstructorIsNotPublic(): void
    {
        $constructor = (new \ReflectionClass(DateTime::class))->getConstructor();

        self::assertNotNull($constructor);
        self::assertFalse($constructor->isPublic());
    }

    public function testSourceDoesNotUseNewDateTime(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(dirname(__DIR__) . '/src', \FilesystemIterator::SKIP_DOTS),
        );

        $offenders = [];
        foreach ($files as $file) {
            if ($file instanceof \SplFileInfo && $file->getExtension() === 'php') {
                $code = (string) file_get_contents($file->getPathname());
                if (preg_match('/\bnew\s+DateTime\s*\(/', $code) === 1) {
                    $offenders[] = $file->getPathname();
                }
            }
        }

        self::assertSame([], $offenders, 'O src/ nao deve criar DateTime com "new DateTime(".');
    }
}
