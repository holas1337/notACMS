<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

interface ImageConfigInterface
{
    public const array DEFAULT_IMAGE_VARIANT_WIDTHS = [640, 960];

    public const string DEFAULT_IMAGE_MAGICK_FLAGS = '-strip';

    public const int DEFAULT_IMAGE_QUALITY = 82;

    /**
     * @return int[]
     */
    public function getImageVariantWidths(): array;

    public function getImageQuality(): int;

    public function getImageMagickFlags(): string;
}
