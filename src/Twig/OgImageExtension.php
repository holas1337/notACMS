<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use Twig\Attribute\AsTwigFunction;

final readonly class OgImageExtension
{
    #[AsTwigFunction(name: 'og_image_url')]
    public function ogImageUrl(?ContentItem $contentItem, string $siteBaseUrl): string
    {
        if ($contentItem instanceof ContentItem && null !== $contentItem->image()) {
            return $siteBaseUrl.$contentItem->image();
        }

        return $siteBaseUrl.'/build/images/og-default.jpg';
    }
}
