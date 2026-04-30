<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\SiteConfigData;
use PHPUnit\Framework\TestCase;

final class SiteConfigDataTest extends TestCase
{
    public function testFromArrayWithAllFields(): void
    {
        $config = SiteConfigData::fromArray([
            'name' => 'Test Site',
            'base_url' => 'https://test.dev',
            'description' => 'A test site',
            'social' => ['github' => 'https://github.com/test'],
            'author' => ['name' => 'Test Author', 'email' => 'test@example.com'],
            'locales' => ['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']],
        ]);

        self::assertSame('Test Site', $config->name);
        self::assertSame('https://test.dev', $config->baseUrl);
        self::assertSame('A test site', $config->description);
        self::assertSame(['github' => 'https://github.com/test'], $config->social);
        self::assertSame(['name' => 'Test Author', 'email' => 'test@example.com'], $config->author);
        self::assertSame(['en' => ['label' => 'English'], 'pl' => ['label' => 'Polski']], $config->locales);
    }

    public function testFromArrayWithEmptyArray(): void
    {
        $config = SiteConfigData::fromArray([]);

        self::assertSame('', $config->name);
        self::assertSame('', $config->baseUrl);
        self::assertSame('', $config->description);
        self::assertSame([], $config->social);
        self::assertSame([], $config->author);
        self::assertSame([], $config->locales);
    }

    public function testFromArrayWithInvalidTypesCoerces(): void
    {
        $config = SiteConfigData::fromArray([
            'name' => 123,
            'base_url' => null,
            'social' => 'not-an-array',
        ]);

        self::assertSame('123', $config->name);
        self::assertSame('', $config->baseUrl);
        self::assertSame('not-an-array', $config->social);
    }

    public function testTypedPropertiesCoerceDefaults(): void
    {
        $config = SiteConfigData::fromArray([
            'name' => 123,
            'base_url' => null,
            'author' => null,
        ]);

        self::assertSame('123', $config->name);
        self::assertSame('', $config->baseUrl);
        self::assertSame('', $config->description);
        self::assertSame([], $config->author);
        self::assertSame([], $config->locales);
    }

    public function testAllReturnsFullRawArray(): void
    {
        $config = SiteConfigData::fromArray([
            'name' => 'Test Site',
            'custom_field' => 'custom_value',
        ]);

        self::assertSame('custom_value', $config->all()['custom_field']);
        self::assertSame('Test Site', $config->all()['name']);
    }

    public function testArrayAccessRead(): void
    {
        $config = SiteConfigData::fromArray([
            'name' => 'Test Site',
            'custom_field' => 'custom_value',
        ]);

        self::assertSame('custom_value', $config['custom_field']);
        self::assertSame('Test Site', $config['name']);
    }

    public function testArrayAccessReturnsNullForMissingKey(): void
    {
        $config = SiteConfigData::fromArray([]);

        self::assertNull($config['nonexistent']);
    }

    public function testArrayAccessExists(): void
    {
        $config = SiteConfigData::fromArray(['name' => 'Test']);

        self::assertTrue(isset($config['name']));
        self::assertFalse(isset($config['missing']));
    }

    public function testArrayAccessSetThrowsException(): void
    {
        $config = SiteConfigData::fromArray([]);

        $this->expectException(\BadMethodCallException::class);
        $config['new'] = 'value';
    }

    public function testArrayAccessUnsetThrowsException(): void
    {
        $config = SiteConfigData::fromArray(['name' => 'Test']);

        $this->expectException(\BadMethodCallException::class);
        unset($config['name']);
    }

    public function testNestedStructuresPassThrough(): void
    {
        $config = SiteConfigData::fromArray([
            'author' => [
                'name' => 'Author',
                'expertise' => [
                    ['key' => 'backend', 'tags' => ['PHP', 'Symfony']],
                ],
                'recommendations' => [
                    ['name' => 'Someone', 'quote' => 'Great dev'],
                ],
            ],
        ]);

        self::assertSame('Author', $config->author['name']);
        self::assertSame('PHP', $config->author['expertise'][0]['tags'][0]);
        self::assertSame('Great dev', $config->author['recommendations'][0]['quote']);
    }

    public function testDirectConstructionPreservesRaw(): void
    {
        $config = SiteConfigData::fromArray([
            'name' => 'Site',
            'extra' => 42,
        ]);

        self::assertSame('Site', $config->name);
        self::assertSame(42, $config['extra']);
    }
}
