<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ValueObject\LangSwitchContext;

interface LangSwitchUrlResolverInterface
{
    /**
     * @param array<string, array<string, string>> $translationMap
     * @param string[]                             $otherLocales
     *
     * @return array<string, string> locale => URL
     */
    public function resolve(?string $directoryKey, LangSwitchContext $langSwitchContext, array $translationMap, array $otherLocales): array;
}
