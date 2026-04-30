<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Content\ValueObject;

use NotACms\Content\ValueObject\TranslationMapData;
use PHPUnit\Framework\TestCase;

final class TranslationMapDataTest extends TestCase
{
    public function testAllReturnsUnderlyingArray(): void
    {
        $data = new TranslationMapData([
            'about' => ['en' => '/about/', 'pl' => '/o-mnie/'],
        ]);

        self::assertSame([
            'about' => ['en' => '/about/', 'pl' => '/o-mnie/'],
        ], $data->all());
    }

    public function testUrlReturnsCorrectValue(): void
    {
        $data = new TranslationMapData([
            'about' => ['en' => '/about/', 'pl' => '/o-mnie/'],
        ]);

        self::assertSame('/about/', $data->url('about', 'en'));
        self::assertSame('/o-mnie/', $data->url('about', 'pl'));
    }

    public function testUrlReturnsNullForUnknownKey(): void
    {
        $data = new TranslationMapData([]);

        self::assertNull($data->url('missing', 'en'));
    }

    public function testUrlReturnsNullForUnknownLocale(): void
    {
        $data = new TranslationMapData(['about' => ['en' => '/about/']]);

        self::assertNull($data->url('about', 'de'));
    }

    public function testOffsetGetReturnsSubArray(): void
    {
        $data = new TranslationMapData([
            'about' => ['en' => '/about/', 'pl' => '/o-mnie/'],
        ]);

        self::assertSame(['en' => '/about/', 'pl' => '/o-mnie/'], $data['about']);
    }

    public function testOffsetGetReturnsEmptyArrayForUnknownKey(): void
    {
        $data = new TranslationMapData([]);

        self::assertSame([], $data['missing']);
    }

    public function testOffsetExists(): void
    {
        $data = new TranslationMapData(['about' => ['en' => '/about/']]);

        self::assertTrue(isset($data['about']));
        self::assertFalse(isset($data['missing']));
    }

    public function testOffsetSetThrowsException(): void
    {
        $data = new TranslationMapData([]);

        $this->expectException(\BadMethodCallException::class);
        $data['new'] = ['en' => '/new/'];
    }

    public function testOffsetUnsetThrowsException(): void
    {
        $data = new TranslationMapData(['about' => ['en' => '/about/']]);

        $this->expectException(\BadMethodCallException::class);
        unset($data['about']);
    }

    public function testEmptyMap(): void
    {
        $data = new TranslationMapData([]);

        self::assertSame([], $data->all());
        self::assertNull($data->url('any', 'en'));
    }
}
