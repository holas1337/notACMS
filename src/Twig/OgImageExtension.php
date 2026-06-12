<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use Twig\Attribute\AsTwigFunction;

final readonly class OgImageExtension
{
    private const string DEFAULT_OG_IMAGE = '/build/images/og-default.jpg';

    #[AsTwigFunction(name: 'og_image_url')]
    public function ogImageUrl(?ContentItem $contentItem, string $siteBaseUrl): string
    {
        if ($contentItem instanceof ContentItem && null !== $contentItem->image()) {
            return $siteBaseUrl.$contentItem->image();
        }

        return $siteBaseUrl.self::DEFAULT_OG_IMAGE;
    }
}
