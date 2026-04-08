<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Service\Content\ContentServiceInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class ContentTwigExtension
{
    public function __construct(
        private ContentServiceInterface $contentService,
    ) {
    }

    #[AsTwigFunction(name: 'content_url')]
    public function contentUrl(string $directoryKey, string $locale): string
    {
        $contentTree = $this->contentService->getTree($locale);
        $item = $contentTree->findByDirectoryKey($directoryKey);

        return $item?->url() ?? '/';
    }
}
