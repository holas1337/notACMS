<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

use NotACms\Service\SiteConfigServiceInterface;

final readonly class ResponsiveImageService implements ResponsiveImageServiceInterface
{
    public function __construct(
        private SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function getVariantWidths(int $sourceWidth): array
    {
        $sorted = $this->siteConfigService->getImageVariantWidths();
        sort($sorted);

        return array_values(array_filter(
            $sorted,
            fn (int $w): bool => $w < $sourceWidth,
        ));
    }

    public function buildSrcset(string $src, int $sourceWidth): string
    {
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
