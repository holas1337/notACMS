<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service\Image;

use NotACms\Service\Image\ResponsiveImageService;
use NotACms\Service\SiteConfigServiceInterface;
use PHPUnit\Framework\TestCase;

final class ResponsiveImageServiceTest extends TestCase
{
    private ResponsiveImageService $service;
    private SiteConfigServiceInterface $configService;

    protected function setUp(): void
    {
        $this->configService = $this->createStub(SiteConfigServiceInterface::class);
        $this->service = new ResponsiveImageService($this->configService);
    }

    public function testGetVariantWidthsFiltersSmallerWidths(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([320, 640, 960, 1280]);

        $result = $this->service->getVariantWidths(1280);

        self::assertSame([320, 640, 960], $result);
    }

    public function testGetVariantWidthsReturnsEmptyForSourceSmallest(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([320, 640, 960]);

        $result = $this->service->getVariantWidths(320);

        self::assertSame([], $result);
    }

    public function testGetVariantWidthsReturnsEmptyForNoSmallerVariants(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([640, 960]);

        $result = $this->service->getVariantWidths(640);

        self::assertSame([], $result);
    }

    public function testGetVariantWidthsSortsAscending(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([960, 640, 320]);

        $result = $this->service->getVariantWidths(1280);

        self::assertSame([320, 640, 960], $result);
    }

    public function testGetVariantWidthsHandlesEmptyConfig(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([]);

        $result = $this->service->getVariantWidths(1280);

        self::assertSame([], $result);
    }

    public function testBuildSrcsetReturnsEmptyForNoVariants(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([]);

        $result = $this->service->buildSrcset('/image-1280w.webp', 1280);

        self::assertSame('', $result);
    }

    public function testBuildSrcsetIncludesVariantsAndSource(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([640, 960]);

        $result = $this->service->buildSrcset('/media/image/image-1280w.webp', 1280);

        $expected = '/media/image/image-1280w-640w.webp 640w, /media/image/image-1280w-960w.webp 960w, /media/image/image-1280w.webp 1280w';
        self::assertSame($expected, $result);
    }

    public function testBuildSrcsetHandlesDifferentWidths(): void
    {
        $this->configService->method('getImageVariantWidths')
            ->willReturn([320, 480, 640]);

        $result = $this->service->buildSrcset('/image-1280w.webp', 1280);

        $expected = '/image-1280w-320w.webp 320w, /image-1280w-480w.webp 480w, /image-1280w-640w.webp 640w, /image-1280w.webp 1280w';
        self::assertSame($expected, $result);
    }
}
