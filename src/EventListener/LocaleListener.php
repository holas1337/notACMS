<?php

declare(strict_types=1);

namespace NotACms\EventListener;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\LocaleAwareInterface;

// Run after Symfony's LocaleListener (16) and LocaleAwareListener (15),
// so we win and the translator picks up the correct locale.
#[AsEventListener(event: KernelEvents::REQUEST, priority: 8)]
final readonly class LocaleListener
{
    public function __construct(
        private LocaleAwareInterface $localeAware,
        private SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function __invoke(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();
        $path = $request->getPathInfo();

        $locale = $this->siteConfigService->detectLocaleFromPath($path);

        $request->setLocale($locale);
        $this->localeAware->setLocale($locale);
    }
}
