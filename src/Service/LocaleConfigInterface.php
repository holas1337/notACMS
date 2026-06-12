<?php

declare(strict_types=1);

namespace NotACms\Service;

interface LocaleConfigInterface
{
    public const string FALLBACK_LOCALE = 'en';

    /**
     * @return string[] Ordered locale codes, first = default
     */
    public function getLocales(): array;

    public function getDefaultLocale(): string;

    public function detectLocaleFromPath(string $path): string;

    public function getUrlPrefix(string $locale): string;
}
