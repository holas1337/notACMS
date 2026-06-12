<?php

declare(strict_types=1);

namespace NotACms\Service\StaticBuild;

use NotACms\Content\ValueObject\MediaPublishResult;
use NotACms\Service\Image\ImageConfigInterface;
use NotACms\Service\Image\ImageResizerInterface;
use NotACms\Service\Image\ResponsiveImageServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final readonly class MediaPublisher implements MediaPublisherInterface
{
    public function __construct(
        private ImageResizerInterface $imageResizer,
        private ResponsiveImageServiceInterface $responsiveImageService,
        private ImageConfigInterface $imageConfig,
        #[Autowire('%notacms_content%')]
        private string $contentDir,
    ) {
    }

    public function publish(string $outputDir): MediaPublishResult
    {
        $warnings = [];
        $notes = [];

        $copied = $this->copyMediaFiles($outputDir, $warnings, $notes);
        $optimized = $this->optimizeOriginals($outputDir, $notes);
        $variants = $this->generateResponsiveImages($outputDir, $notes);

        return new MediaPublishResult($copied, $optimized, $variants, $warnings, $notes);
    }

    /**
     * @param list<string> $warnings
     * @param list<string> $notes
     */
    private function copyMediaFiles(string $outputDir, array &$warnings, array &$notes): int
    {
        $finder = new Finder()->directories()->in($this->contentDir)->name('files');
        $filesystem = new Filesystem();

        $copied = 0;
        $copiedSources = [];
        foreach ($finder as $directory) {
            $postDirName = basename($directory->getPath());
            if (isset($copiedSources[$postDirName])) {
                $warnings[] = sprintf(
                    'Media directory name collision: "%s" (%s and %s) — later files overwrite earlier ones in /media/%s/',
                    $postDirName,
                    $copiedSources[$postDirName],
                    $directory->getPath(),
                    $postDirName,
                );
            }

            $copiedSources[$postDirName] = $directory->getPath();
            $filesystem->mirror($directory->getRealPath(), $outputDir.'/media/'.$postDirName);
            ++$copied;
            $notes[] = sprintf('media: %s/', $postDirName);
        }

        return $copied;
    }

    /**
     * @param list<string> $notes
     */
    private function optimizeOriginals(string $outputDir, array &$notes): int
    {
        $mediaDir = $outputDir.'/media';
        if (!is_dir($mediaDir)) {
            return 0;
        }

        $optimized = 0;
        foreach (new Finder()->files()->in($mediaDir)->name('*.webp') as $finder) {
            if ($this->isVariantFile($finder->getFilenameWithoutExtension())) {
                continue;
            }

            $this->imageResizer->optimize($finder->getRealPath());
            ++$optimized;
            $notes[] = sprintf('optimized: %s/%s', basename($finder->getPath()), $finder->getFilename());
        }

        return $optimized;
    }

    /**
     * @param list<string> $notes
     */
    private function generateResponsiveImages(string $outputDir, array &$notes): int
    {
        $mediaDir = $outputDir.'/media';
        if (!is_dir($mediaDir)) {
            return 0;
        }

        $generated = 0;
        foreach (new Finder()->files()->in($mediaDir)->name('*.webp') as $finder) {
            $baseName = $finder->getFilenameWithoutExtension();
            if ($this->isVariantFile($baseName)) {
                continue;
            }

            $imageInfo = @getimagesize($finder->getRealPath());
            if (false === $imageInfo) {
                continue;
            }

            $directory = $finder->getPath();
            foreach ($this->responsiveImageService->getVariantWidths($imageInfo[0]) as $variantWidth) {
                $this->imageResizer->resize($finder->getRealPath(), $directory.'/'.$baseName.'-'.$variantWidth.'w.webp', $variantWidth);
                ++$generated;
                $notes[] = sprintf('variant: %s/%s-%dw.webp', basename($directory), $baseName, $variantWidth);
            }
        }

        return $generated;
    }

    private function isVariantFile(string $baseName): bool
    {
        return array_any($this->imageConfig->getImageVariantWidths(), fn ($width): bool => str_ends_with($baseName, '-'.$width.'w'));
    }
}
