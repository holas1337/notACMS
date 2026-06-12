<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\Enum\FilterType;
use NotACms\Content\ValueObject\LangSwitchContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class LangSwitchUrlResolver implements LangSwitchUrlResolverInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function resolve(?string $directoryKey, LangSwitchContext $langSwitchContext, array $translationMap, array $otherLocales): array
    {
        $translationsForKey = null !== $directoryKey ? ($translationMap[$directoryKey] ?? []) : [];

        $result = [];
        foreach ($otherLocales as $otherLocale) {
            $result[$otherLocale] = $translationsForKey[$otherLocale]
                ?? $langSwitchContext->urlOverrides[$otherLocale]
                ?? $this->fallbackUrl($langSwitchContext, $otherLocale);
        }

        return $result;
    }

    private function fallbackUrl(LangSwitchContext $langSwitchContext, string $otherLocale): string
    {
        if (FilterType::Archive->value === $langSwitchContext->filterType && null !== $langSwitchContext->archiveYear) {
            if (null !== $langSwitchContext->archiveMonth) {
                return $this->urlGenerator->generate('blog_archive_'.$otherLocale, [
                    'year' => $langSwitchContext->archiveYear,
                    'month' => sprintf('%02d', $langSwitchContext->archiveMonth),
                ], UrlGeneratorInterface::ABSOLUTE_PATH);
            }

            return $this->urlGenerator->generate('blog_archive_year_'.$otherLocale, ['year' => $langSwitchContext->archiveYear], UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        if (null !== $langSwitchContext->currentPage && null === $langSwitchContext->filterType) {
            if (1 < $langSwitchContext->currentPage) {
                return $this->urlGenerator->generate('blog_list_paginated_'.$otherLocale, ['page' => $langSwitchContext->currentPage], UrlGeneratorInterface::ABSOLUTE_PATH);
            }

            return $this->urlGenerator->generate('blog_list_'.$otherLocale, [], UrlGeneratorInterface::ABSOLUTE_PATH);
        }

        return $this->urlGenerator->generate('home_'.$otherLocale, [], UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
