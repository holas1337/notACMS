<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ValueObject\SidebarData;
use NotACms\Service\Content\SidebarDataProviderInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class SidebarExtension
{
    public function __construct(
        private SidebarDataProviderInterface $sidebarDataProvider,
    ) {
    }

    #[AsTwigFunction(name: 'sidebar_data')]
    public function getSidebarData(string $locale): ?SidebarData
    {
        try {
            return $this->sidebarDataProvider->getData($locale);
        } catch (\Throwable) {
            return null;
        }
    }
}
