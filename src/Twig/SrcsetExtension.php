<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Service\Image\MediaFileResolverInterface;
use NotACms\Service\Image\ResponsiveImageServiceInterface;
use Twig\Attribute\AsTwigFilter;

final class SrcsetExtension
{
    private const string DEFAULT_SIZES = '(max-width: 48em) 100vw, 720px';

    /** @var array<string, int|null> */
    private array $widthCache = [];

    public function __construct(
        private readonly MediaFileResolverInterface $mediaFileResolver,
        private readonly ResponsiveImageServiceInterface $responsiveImageService,
    ) {
    }

    #[AsTwigFilter(name: 'srcset_media')]
    public function srcsetMedia(string $html): string
    {
        $result = preg_replace_callback(
            '/<img(\s[^>]*)src="(\/media\/[^"]+\.webp)"([^>]*)>/i',
            function (array $matches): string {
                $before = $matches[1];
                $src = $matches[2];
                $after = $matches[3];

                if (str_contains($before, 'srcset') || str_contains($after, 'srcset')) {
                    return $matches[0];
                }

                $width = $this->getSourceWidth($src);

                if (null === $width) {
                    return $matches[0];
                }

                $srcset = $this->responsiveImageService->buildSrcset($src, $width);

                if ('' === $srcset) {
                    return $matches[0];
                }

                return sprintf(
                    '<img%ssrc="%s" srcset="%s" sizes="%s"%s>',
                    $before,
                    $src,
                    $srcset,
                    self::DEFAULT_SIZES,
                    $after,
                );
            },
            $html,
        );

        return $result ?? $html;
    }

    private function getSourceWidth(string $mediaSrc): ?int
    {
        if (array_key_exists($mediaSrc, $this->widthCache)) {
            return $this->widthCache[$mediaSrc];
        }

        $parts = explode('/', ltrim($mediaSrc, '/'));

        if (3 > count($parts) || 'media' !== $parts[0]) {
            return $this->widthCache[$mediaSrc] = null;
        }

        $postDir = $parts[1];
        $filename = implode('/', array_slice($parts, 2));

        $path = $this->mediaFileResolver->resolve($postDir, $filename);

        if (null === $path) {
            return $this->widthCache[$mediaSrc] = null;
        }

        $info = getimagesize($path);

        return $this->widthCache[$mediaSrc] = false !== $info ? $info[0] : null;
    }
}
