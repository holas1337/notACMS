<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
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

    #[AsTwigFunction(name: 'content_item')]
    public function contentItem(string $directoryKey, string $locale): ?ContentItem
    {
        $contentTree = $this->contentService->getTree($locale);

        return $contentTree->findByDirectoryKey($directoryKey);
    }
}
