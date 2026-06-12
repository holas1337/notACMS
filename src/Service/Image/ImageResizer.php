<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final readonly class ImageResizer implements ImageResizerInterface
{
    public function __construct(
        private ImageConfigInterface $imageConfig,
    ) {
    }

    public function optimize(string $path, ?int $quality = null): void
    {
        $quality ??= $this->imageConfig->getImageQuality();

        $this->runMagick(
            ['magick', $path, '-quality', (string) $quality, ...$this->flags(), $path],
            sprintf('ImageMagick optimize failed for %s', $path),
        );
    }

    public function resize(string $sourcePath, string $targetPath, int $width, ?int $quality = null): void
    {
        $quality ??= $this->imageConfig->getImageQuality();
        new Filesystem()->mkdir(dirname($targetPath));

        $this->runMagick(
            ['magick', $sourcePath, '-resize', $width.'x', '-quality', (string) $quality, ...$this->flags(), $targetPath],
            sprintf('ImageMagick resize failed for %s', $sourcePath),
        );
    }

    /**
     * @param list<string> $commandLine
     */
    private function runMagick(array $commandLine, string $errorPrefix): void
    {
        $process = new Process($commandLine);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('%s (exit %d): %s', $errorPrefix, (int) $process->getExitCode(), $process->getErrorOutput().$process->getOutput()));
        }
    }

    /**
     * @return list<string>
     */
    private function flags(): array
    {
        return array_values(array_filter(explode(' ', $this->imageConfig->getImageMagickFlags())));
    }
}
