<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Service\SiteConfigServiceInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class SiteConfigExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function getGlobals(): array
    {
        $site = $this->siteConfigService->getSiteConfig();

        return [
            'site_name' => $site->name,
            'site_base_url' => $site->baseUrl,
            'site_description' => $site->description,
            'site_social' => $site->social,
            'site_author' => $site->author,
            'site_locales' => $site->locales,
            'site_default_locale' => $this->siteConfigService->getDefaultLocale(),
            'site_locales_list' => $this->siteConfigService->getLocales(),
            'image_variant_widths' => $this->siteConfigService->getImageVariantWidths(),
            'new_post_days' => $this->siteConfigService->getNewPostDays(),
            'coming_soon_reveal_days' => $this->siteConfigService->getComingSoonRevealDays(),
            'meta_description_length' => $this->siteConfigService->getMetaDescriptionLength(),
        ];
    }
}
