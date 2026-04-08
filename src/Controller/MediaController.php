<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Content\ValueObject\ParsedVariant;
use NotACms\Service\Image\ImageResizerInterface;
use NotACms\Service\Image\MediaFileResolverInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/media/{dirKey}/{filename}', name: 'media_file', requirements: ['dirKey' => '[a-z0-9_-]+', 'filename' => '[^/]+'])]
final readonly class MediaController
{
    /** @var array<string, int> suffix → target width */
    private array $variantWidthMap;

    public function __construct(
        #[Autowire('%kernel.cache_dir%')]
        private string $cacheDir,
        private ImageResizerInterface $imageResizer,
        private MediaFileResolverInterface $mediaFileResolver,
        private SiteConfigServiceInterface $siteConfigService,
    ) {
        $map = [];
        foreach ($this->siteConfigService->getImageVariantWidths() as $imageVariantWidth) {
            $map['-'.$imageVariantWidth.'w'] = $imageVariantWidth;
        }

        $this->variantWidthMap = $map;
    }

    public function __invoke(string $dirKey, string $filename): BinaryFileResponse
    {
        $parsedVariant = $this->parseVariant($filename);

        $originalPath = $this->mediaFileResolver->resolve($dirKey, $parsedVariant->originalFilename)
            ?? throw new NotFoundHttpException(sprintf('Media file not found: %s/%s', $dirKey, $parsedVariant->originalFilename));

        if (null !== $parsedVariant->variantWidth) {
            return $this->serveVariant($originalPath, $dirKey, $filename, $parsedVariant->variantWidth);
        }

        return new BinaryFileResponse($originalPath);
    }

    private function parseVariant(string $filename): ParsedVariant
    {
        $baseName = pathinfo($filename, \PATHINFO_FILENAME);

        foreach ($this->variantWidthMap as $suffix => $width) {
            if (str_ends_with($baseName, $suffix)) {
                $originalBase = substr($baseName, 0, -\strlen($suffix));

                return new ParsedVariant($originalBase.'.webp', $width);
            }
        }

        return new ParsedVariant($filename, null);
    }

    private function serveVariant(string $originalPath, string $dirKey, string $variantFilename, int $width): BinaryFileResponse
    {
        $variantPath = $this->cacheDir.'/media-variants/'.$dirKey.'/'.$variantFilename;

        $realCacheDir = realpath($this->cacheDir);
        if (false === $realCacheDir || !str_starts_with(realpath(dirname($variantPath)) ?: dirname($variantPath), $realCacheDir)) {
            throw new NotFoundHttpException('Invalid variant path');
        }

        if (!is_file($variantPath)) {
            $this->imageResizer->resize($originalPath, $variantPath, $width);
        }

        if (!is_file($variantPath)) {
            return new BinaryFileResponse($originalPath);
        }

        return new BinaryFileResponse($variantPath);
    }
}
