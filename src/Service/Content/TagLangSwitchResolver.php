<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Service\LocaleConfigInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class TagLangSwitchResolver implements TagLangSwitchResolverInterface
{
    public function __construct(
        private ContentServiceInterface $contentService,
        private TagTranslationServiceInterface $tagTranslationService,
        private LocaleConfigInterface $localeConfig,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(string $tag, string $locale): array
    {
        $urls = [];

        foreach ($this->localeConfig->getLocales() as $otherLocale) {
            if ($otherLocale === $locale) {
                continue;
            }

            $otherTag = $this->tagTranslationService->translate($tag, $locale, $otherLocale);
            $urls[$otherLocale] = [] === $this->contentService->getPostsByTag($otherTag, $otherLocale)
                ? $this->urlGenerator->generate('blog_list_'.$otherLocale)
                : $this->urlGenerator->generate('blog_tag_'.$otherLocale, ['tag' => $otherTag]);
        }

        return $urls;
    }
}
