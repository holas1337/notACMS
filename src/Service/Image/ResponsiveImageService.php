<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

final readonly class ResponsiveImageService implements ResponsiveImageServiceInterface
{
    public function __construct(
        private ImageConfigInterface $imageConfig,
    ) {
    }

    public function getVariantWidths(int $sourceWidth): array
    {
        $sorted = $this->imageConfig->getImageVariantWidths();
        sort($sorted);

        return array_values(array_filter(
            $sorted,
            fn (int $width): bool => $width < $sourceWidth,
        ));
    }

    public function buildSrcset(string $src, int $sourceWidth): string
    {
        if (!str_ends_with($src, '.webp')) {
            return '';
        }

        $widths = $this->getVariantWidths($sourceWidth);
        if ([] === $widths) {
            return '';
        }

        $base = substr($src, 0, -5);
        $parts = [];
        foreach ($widths as $width) {
            $parts[] = sprintf('%s-%dw.webp %dw', $base, $width, $width);
        }

        $parts[] = sprintf('%s %dw', $src, $sourceWidth);

        return implode(', ', $parts);
    }
}
