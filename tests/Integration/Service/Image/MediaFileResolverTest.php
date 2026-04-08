<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Image;

use NotACms\Service\Image\MediaFileResolver;
use NotACms\Service\Image\MediaFileResolverInterface;
use NotACms\Tests\TmpDirTrait;
use PHPUnit\Framework\TestCase;

final class MediaFileResolverTest extends TestCase
{
    use TmpDirTrait;
    private string $tmpDir;

    private MediaFileResolverInterface $resolver;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/notacms_resolver_' . uniqid();
        mkdir($this->tmpDir . '/my-post/files', 0755, true);
        file_put_contents($this->tmpDir . '/my-post/files/image.webp', 'fake-image-content');

        $this->resolver = new MediaFileResolver($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testResolvesValidFile(): void
    {
        $result = $this->resolver->resolve('my-post', 'image.webp');

        self::assertNotNull($result);
        self::assertStringEndsWith('/my-post/files/image.webp', $result);
        self::assertFileExists($result);
    }

    public function testReturnsNullForMissingFile(): void
    {
        $result = $this->resolver->resolve('my-post', 'nonexistent.webp');

        self::assertNull($result);
    }

    public function testReturnsNullForMissingDirectory(): void
    {
        $result = $this->resolver->resolve('unknown-post', 'image.webp');

        self::assertNull($result);
    }

    public function testReturnsNullForPathTraversal(): void
    {
        $result = $this->resolver->resolve('../etc', 'passwd');

        self::assertNull($result);
    }

    public function testReturnsNullForPathTraversalInFilename(): void
    {
        $result = $this->resolver->resolve('my-post', '../../etc/passwd');

        self::assertNull($result);
    }
}
