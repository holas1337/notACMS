<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

interface ResponsiveImageServiceInterface
{
    /** @return int[] Variant widths to generate, e.g. [640] or [640, 960] or [] */
    public function getVariantWidths(int $sourceWidth): array;

    /** Returns srcset attribute value, or '' if no variants apply */
    public function buildSrcset(string $src, int $sourceWidth): string;
}
