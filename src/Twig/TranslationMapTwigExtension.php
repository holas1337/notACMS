<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Service\Content\ContentServiceInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class TranslationMapTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'translation_map' => $this->contentService->getTranslationMap(),
        ];
    }
}
