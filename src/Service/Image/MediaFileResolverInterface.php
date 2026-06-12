<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

interface MediaFileResolverInterface
{
    /**
     * Returns the real absolute path to a content media file, or null if the file
     * does not exist or if a path-traversal attempt is detected.
     */
    public function resolve(string $directoryKey, string $filename): ?string;
}
