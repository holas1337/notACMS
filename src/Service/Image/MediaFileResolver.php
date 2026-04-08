<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

final readonly class MediaFileResolver implements MediaFileResolverInterface
{
    public function __construct(
        #[Autowire('%notacms_content%')]
        private string $contentDir,
    ) {
    }

    public function resolve(string $dirKey, string $filename): ?string
    {
        $allowedBase = realpath($this->contentDir);

        if (false === $allowedBase) {
            return null;
        }

        $finder = new Finder()->directories()->in($this->contentDir)->name($dirKey);

        foreach ($finder as $dir) {
            $filePath = $dir->getRealPath().'/files/'.$filename;

            if (!is_file($filePath)) {
                continue;
            }

            $realPath = realpath($filePath);

            if (false === $realPath || !str_starts_with($realPath, $allowedBase.\DIRECTORY_SEPARATOR)) {
                return null;
            }

            return $realPath;
        }

        return null;
    }
}
