<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

interface TagTranslationServiceInterface
{
    public function translate(string $tag, string $fromLocale, string $toLocale): string;
}
