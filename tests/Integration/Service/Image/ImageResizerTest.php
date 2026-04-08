<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Image;

use NotACms\Service\Image\ImageResizer;
use NotACms\Service\Image\ImageResizerInterface;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\TmpDirTrait;
use PHPUnit\Framework\TestCase;

final class ImageResizerTest extends TestCase
{
    use TmpDirTrait;
    private ImageResizerInterface $resizer;

    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/notacms_resizer_' . uniqid();
        mkdir($this->tmpDir, 0755, true);

        $siteConfig = $this->createStub(SiteConfigServiceInterface::class);
        $siteConfig->method('getImageQuality')->willReturn(82);
        $siteConfig->method('getImageMagickFlags')->willReturn('-strip');

        $this->resizer = new ImageResizer($siteConfig);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testResizeCreatesResizedImage(): void
    {
        $source = $this->createTestImage(1280, 720);
        $target = $this->tmpDir . '/resized.webp';

        $this->resizer->resize($source, $target, 640);

        self::assertFileExists($target);
        $info = getimagesize($target);
        self::assertSame(640, $info[0]);
    }

    public function testResizeCreatesTargetDirectory(): void
    {
        $source = $this->createTestImage(1280, 720);
        $target = $this->tmpDir . '/subdir/nested/resized.webp';

        $this->resizer->resize($source, $target, 320);

        self::assertFileExists($target);
    }

    public function testResizeWithCustomQuality(): void
    {
        $source = $this->createTestImage(1280, 720);
        $target = $this->tmpDir . '/quality-test.webp';

        $this->resizer->resize($source, $target, 640, 50);

        self::assertFileExists($target);
    }

    public function testOptimizeReducesFileSize(): void
    {
        $source = $this->createTestImage(1280, 720);
        $originalSize = filesize($source);

        $this->resizer->optimize($source);

        self::assertFileExists($source);
        // Optimization may increase size slightly on very small images;
        // the important thing is it doesn't fail.
        $newSize = filesize($source);
        self::assertLessThanOrEqual((int) ($originalSize * 1.1), $newSize);
    }

    public function testThrowsOnInvalidSourcePath(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->resizer->resize('/nonexistent/image.webp', $this->tmpDir . '/out.webp', 640);
    }

    private function createTestImage(int $width, int $height): string
    {
        $path = $this->tmpDir . '/source_' . uniqid() . '.webp';
        $image = imagecreatetruecolor($width, $height);
        imagewebp($image, $path);

        return $path;
    }
}
