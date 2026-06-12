<?php

declare(strict_types=1);

namespace NotACms\Service\Image;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;

final class MediaFileResolver implements MediaFileResolverInterface
{
    /** @var array<string, list<string>> */
    private array $directoryCache = [];

    public function __construct(
        #[Autowire('%notacms_content%')]
        private readonly string $contentDir,
    ) {
    }

    public function resolve(string $directoryKey, string $filename): ?string
    {
        $allowedBase = realpath($this->contentDir);

        if (false === $allowedBase) {
            return null;
        }

        foreach ($this->directoriesFor($directoryKey) as $directoryPath) {
            $filePath = $directoryPath.'/files/'.$filename;

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

    /**
     * @return list<string>
     */
    private function directoriesFor(string $directoryKey): array
    {
        if (!array_key_exists($directoryKey, $this->directoryCache)) {
            $directories = [];
            $finder = new Finder()->directories()->in($this->contentDir)->name($directoryKey);
            foreach ($finder as $directory) {
                $realPath = $directory->getRealPath();
                if (false !== $realPath) {
                    $directories[] = $realPath;
                }
            }

            $this->directoryCache[$directoryKey] = $directories;
        }

        return $this->directoryCache[$directoryKey];
    }
}
