<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Content\ValueObject\ParsedVariant;
use NotACms\Service\Image\ImageConfigInterface;
use NotACms\Service\Image\ImageResizerInterface;
use NotACms\Service\Image\MediaFileResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/media/{directoryKey}/{filename}', name: 'media_file', requirements: ['directoryKey' => '[a-z0-9_-]+', 'filename' => '[^/]+'])]
final class MediaController
{
    /** @var array<string, int>|null suffix → target width */
    private ?array $variantWidthMap = null;

    public function __construct(
        #[Autowire('%kernel.cache_dir%')]
        private readonly string $cacheDir,
        private readonly ImageResizerInterface $imageResizer,
        private readonly MediaFileResolverInterface $mediaFileResolver,
        private readonly ImageConfigInterface $imageConfig,
    ) {
    }

    public function __invoke(string $directoryKey, string $filename): BinaryFileResponse
    {
        $parsedVariant = $this->parseVariant($filename);

        if (null !== $parsedVariant->variantWidth) {
            $originalPath = $this->mediaFileResolver->resolve($directoryKey, $parsedVariant->originalFilename);
            if (null !== $originalPath) {
                return $this->serveVariant($originalPath, $directoryKey, $filename, $parsedVariant->variantWidth);
            }
        }

        $path = $this->mediaFileResolver->resolve($directoryKey, $filename)
            ?? throw new NotFoundHttpException(sprintf('Media file not found: %s/%s', $directoryKey, $filename));

        return new BinaryFileResponse($path);
    }

    private function parseVariant(string $filename): ParsedVariant
    {
        $baseName = pathinfo($filename, \PATHINFO_FILENAME);

        foreach ($this->variantWidthMap() as $suffix => $width) {
            if (str_ends_with($baseName, $suffix)) {
                $originalBase = substr($baseName, 0, -\strlen($suffix));

                return new ParsedVariant($originalBase.'.webp', $width);
            }
        }

        return new ParsedVariant($filename, null);
    }

    /**
     * @return array<string, int>
     */
    private function variantWidthMap(): array
    {
        if (null === $this->variantWidthMap) {
            $map = [];
            foreach ($this->imageConfig->getImageVariantWidths() as $imageVariantWidth) {
                $map['-'.$imageVariantWidth.'w'] = $imageVariantWidth;
            }

            $this->variantWidthMap = $map;
        }

        return $this->variantWidthMap;
    }

    private function serveVariant(string $originalPath, string $directoryKey, string $variantFilename, int $width): BinaryFileResponse
    {
        $variantPath = $this->cacheDir.'/media-variants/'.$directoryKey.'/'.$variantFilename;

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
