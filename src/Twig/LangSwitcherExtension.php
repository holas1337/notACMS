<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use NotACms\Content\Enum\FilterType;
use NotACms\Content\ValueObject\TranslationMapData;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class LangSwitcherExtension
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string>        $otherLocales
     *
     * @return array<string, string>
     */
    #[AsTwigFunction(name: 'lang_switch_urls', needsContext: true)]
    public function langSwitchUrls(array $context, array $otherLocales): array
    {
        $raw = $context['translation_map'] ?? null;
        $translationMap = ($raw instanceof TranslationMapData) ? $raw->all() : [];
        $switchContent = $context['content'] ?? $context['index_content'] ?? null;
        $directoryKey = ($switchContent instanceof ContentItem) ? $switchContent->directoryKey() : null;
        $langSwitchUrl = $context['lang_switch_url'] ?? null;
        $filterType = $context['filter_type'] ?? null;
        $currentPage = $context['current_page'] ?? null;
        $archiveYear = $context['archive_year'] ?? null;
        $archiveMonth = $context['archive_month'] ?? null;

        $result = [];
        $isFirst = true;

        foreach ($otherLocales as $otherLocale) {
            $url = null;

            if (null !== $directoryKey && isset($translationMap[$directoryKey][$otherLocale])) {
                $url = $translationMap[$directoryKey][$otherLocale];
            }

            if (null === $url && $isFirst && null !== $langSwitchUrl) {
                $url = $langSwitchUrl;
            }

            if (null === $url) {
                if (FilterType::Archive->value === $filterType && null !== $archiveYear && null !== $archiveMonth) {
                    $url = $this->urlGenerator->generate(
                        'blog_archive_'.$otherLocale,
                        ['year' => $archiveYear, 'month' => sprintf('%02d', $archiveMonth)],
                        UrlGeneratorInterface::ABSOLUTE_PATH,
                    );
                } elseif (null !== $currentPage && 1 < $currentPage && null === $filterType) {
                    $url = $this->urlGenerator->generate(
                        'blog_list_paginated_'.$otherLocale,
                        ['page' => $currentPage],
                        UrlGeneratorInterface::ABSOLUTE_PATH,
                    );
                } elseif (null !== $currentPage && null === $filterType) {
                    $url = $this->urlGenerator->generate(
                        'blog_list_'.$otherLocale,
                        [],
                        UrlGeneratorInterface::ABSOLUTE_PATH,
                    );
                } else {
                    $url = $this->urlGenerator->generate(
                        'home_'.$otherLocale,
                        [],
                        UrlGeneratorInterface::ABSOLUTE_PATH,
                    );
                }
            }

            $result[$otherLocale] = $url;
            $isFirst = false;
        }

        return $result;
    }
}
