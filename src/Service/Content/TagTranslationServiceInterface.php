<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

interface TagTranslationServiceInterface
{
    public const string TAGS_FILENAME = '_tags.yaml';

    public function translate(string $tag, string $fromLocale, string $toLocale): string;
}
