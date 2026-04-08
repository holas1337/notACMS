<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

interface ImageResizerInterface
{
    public function optimize(string $path, ?int $quality = null): void;

    public function resize(string $sourcePath, string $targetPath, int $width, ?int $quality = null): void;
}
