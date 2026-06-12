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
        $item = $this->contentService->findByDirectoryKey($directoryKey, $locale);

        return $item?->url() ?? '/';
    }

    #[AsTwigFunction(name: 'content_item')]
    public function contentItem(string $directoryKey, string $locale): ?ContentItem
    {
        return $this->contentService->findByDirectoryKey($directoryKey, $locale);
    }
}
