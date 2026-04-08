<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Service\Image\MediaFileResolverInterface;
use NotACms\Service\Image\ResponsiveImageServiceInterface;
use NotACms\Twig\SrcsetExtension;
use PHPUnit\Framework\TestCase;

final class SrcsetExtensionTest extends TestCase
{
    private MediaFileResolverInterface $mediaFileResolver;

    private ResponsiveImageServiceInterface $responsiveImageService;

    private SrcsetExtension $extension;

    protected function setUp(): void
    {
        $this->mediaFileResolver = $this->createStub(MediaFileResolverInterface::class);
        $this->responsiveImageService = $this->createStub(ResponsiveImageServiceInterface::class);
        $this->extension = new SrcsetExtension($this->mediaFileResolver, $this->responsiveImageService);
    }

    public function testAddsSrcsetToMediaImage(): void
    {
        $tmpFile = $this->createTestImage(1280, 720);
        $this->mediaFileResolver->method('resolve')
            ->willReturn($tmpFile);
        $this->responsiveImageService->method('buildSrcset')
            ->willReturn('/media/post/img-640w.webp 640w, /media/post/img.webp 1280w');

        $html = '<img src="/media/post/img.webp" alt="Test">';
        $result = $this->extension->srcsetMedia($html);

        self::assertStringContainsString('srcset="/media/post/img-640w.webp 640w, /media/post/img.webp 1280w"', $result);
        self::assertStringContainsString('sizes="(max-width: 48em) 100vw, 720px"', $result);

        unlink($tmpFile);
    }

    public function testSkipsImageWithExistingSrcset(): void
    {
        $html = '<img src="/media/post/img.webp" srcset="/media/post/img-640w.webp 640w" alt="Test">';
        $result = $this->extension->srcsetMedia($html);

        self::assertSame($html, $result);
    }

    public function testSkipsNonMediaPaths(): void
    {
        $html = '<img src="/images/photo.jpg" alt="Test">';
        $result = $this->extension->srcsetMedia($html);

        self::assertSame($html, $result);
    }

    public function testSkipsImageWhenResolverReturnsNull(): void
    {
        $this->mediaFileResolver->method('resolve')
            ->willReturn(null);

        $html = '<img src="/media/post/img.webp" alt="Test">';
        $result = $this->extension->srcsetMedia($html);

        self::assertSame($html, $result);
    }

    public function testSkipsImageWhenSrcsetIsEmpty(): void
    {
        $tmpFile = $this->createTestImage(1280, 720);
        $this->mediaFileResolver->method('resolve')
            ->willReturn($tmpFile);
        $this->responsiveImageService->method('buildSrcset')
            ->willReturn('');

        $html = '<img src="/media/post/img.webp" alt="Test">';
        $result = $this->extension->srcsetMedia($html);

        self::assertSame($html, $result);

        unlink($tmpFile);
    }

    public function testCachesImageWidth(): void
    {
        $tmpFile = $this->createTestImage(1280, 720);
        $this->mediaFileResolver->method('resolve')
            ->willReturn($tmpFile);
        $this->responsiveImageService->method('buildSrcset')
            ->willReturn('/media/post/img-640w.webp 640w, /media/post/img.webp 1280w');

        $html1 = '<img src="/media/post/img.webp" alt="First">';
        $html2 = '<img src="/media/post/img.webp" alt="Second">';

        $result1 = $this->extension->srcsetMedia($html1);
        $result2 = $this->extension->srcsetMedia($html2);

        self::assertStringContainsString('srcset=', $result1);
        self::assertStringContainsString('srcset=', $result2);

        unlink($tmpFile);
    }

    public function testHandlesMultipleImagesInHtml(): void
    {
        $tmpFile1 = $this->createTestImage(1280, 720);
        $tmpFile2 = $this->createTestImage(640, 480);

        $this->mediaFileResolver->method('resolve')
            ->willReturnMap([
                ['post', 'img1.webp', $tmpFile1],
                ['post', 'img2.webp', $tmpFile2],
            ]);
        $this->responsiveImageService->method('buildSrcset')
            ->willReturn('/media/post/img-640w.webp 640w, /media/post/img.webp 1280w');

        $html = '<img src="/media/post/img1.webp" alt="First"><img src="/media/post/img2.webp" alt="Second">';
        $result = $this->extension->srcsetMedia($html);

        self::assertStringContainsString('srcset=', $result);

        unlink($tmpFile1);
        unlink($tmpFile2);
    }

    public function testHandlesSrcsetInAfterAttributes(): void
    {
        $html = '<img src="/media/post/img.webp" alt="Test" srcset="existing">';
        $result = $this->extension->srcsetMedia($html);

        self::assertSame($html, $result);
    }

    /**
     * Create a minimal valid WebP image file.
     */
    private function createTestImage(int $width, int $height): string
    {
        $path = sys_get_temp_dir() . '/notacms_test_' . uniqid() . '.webp';
        $image = imagecreatetruecolor($width, $height);
        imagewebp($image, $path);

        return $path;
    }
}
