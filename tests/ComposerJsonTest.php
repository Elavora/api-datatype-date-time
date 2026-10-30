<?php

declare(strict_types=1);

namespace Elavora\Api\DataTypes\DateTime\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Campos do composer.json do pacote depois do upgrade para o core 2.
 *
 * @see docs/USO.md#versão-e-compatibilidade
 * @see docs/AUTOMACAO.md#composerjson
 */
final class ComposerJsonTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $composer;

    protected function setUp(): void
    {
        $this->composer = json_decode(
            (string) file_get_contents(dirname(__DIR__) . '/composer.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    public function testRequiresPhp83(): void
    {
        self::assertSame('>=8.3', $this->composer['require']['php'] ?? null);
    }

    public function testRequiresCore2(): void
    {
        self::assertSame('^2.0', $this->composer['require']['elavora/api-datatype-core'] ?? null);
    }

    public function testBranchAliasIs10Dev(): void
    {
        self::assertSame('1.0-dev', $this->composer['extra']['branch-alias']['dev-main'] ?? null);
    }

    public function testAutoloadIsUnchanged(): void
    {
        self::assertSame(
            ['Elavora\\Api\\DataTypes\\' => 'src/'],
            $this->composer['autoload']['psr-4'] ?? null,
        );
    }

    public function testHasNoRepositories(): void
    {
        self::assertArrayNotHasKey('repositories', $this->composer);
    }
}
