<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\Filesystem\Filesystem;

final readonly class ImageResizer implements ImageResizerInterface
{
    public function __construct(
        private SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function optimize(string $path, ?int $quality = null): void
    {
        $quality ??= $this->siteConfigService->getImageQuality();
        $output = [];
        $returnCode = 0;
        exec(
            'magick '
            .escapeshellarg($path)
            .' -quality '.escapeshellarg((string) $quality)
            .' '.$this->escapedFlags()
            .' '.escapeshellarg($path).' 2>&1',
            $output,
            $returnCode,
        );

        if (0 !== $returnCode) {
            throw new \RuntimeException(sprintf('ImageMagick optimize failed for %s (exit %d): %s', $path, $returnCode, implode("\n", $output)));
        }
    }

    public function resize(string $sourcePath, string $targetPath, int $width, ?int $quality = null): void
    {
        $quality ??= $this->siteConfigService->getImageQuality();
        new Filesystem()->mkdir(dirname($targetPath));

        $output = [];
        $returnCode = 0;
        exec(
            'magick '
            .escapeshellarg($sourcePath)
            .' -resize '.escapeshellarg($width.'x')
            .' -quality '.escapeshellarg((string) $quality)
            .' '.$this->escapedFlags()
            .' '.escapeshellarg($targetPath).' 2>&1',
            $output,
            $returnCode,
        );

        if (0 !== $returnCode) {
            throw new \RuntimeException(sprintf('ImageMagick resize failed for %s (exit %d): %s', $sourcePath, $returnCode, implode("\n", $output)));
        }
    }

    private function escapedFlags(): string
    {
        return implode(' ', array_map(
            escapeshellarg(...),
            array_filter(explode(' ', $this->siteConfigService->getImageMagickFlags()))
        ));
    }
}
