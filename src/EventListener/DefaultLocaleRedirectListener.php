<?php

declare(strict_types=1);

namespace NotACms\EventListener;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// Run BEFORE Symfony's RouterListener (priority 32) so we can short-circuit
// before routing 404s on default-locale-prefixed URLs.
#[AsEventListener(event: KernelEvents::REQUEST, priority: 34)]
final readonly class DefaultLocaleRedirectListener
{
    public function __construct(
        private SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function __invoke(RequestEvent $requestEvent): void
    {
        if (!$requestEvent->isMainRequest()) {
            return;
        }

        $request = $requestEvent->getRequest();
        $path = $request->getPathInfo();
        $default = $this->siteConfigService->getDefaultLocale();
        $prefix = '/'.$default;

        if ($path !== $prefix && !str_starts_with($path, $prefix.'/')) {
            return;
        }

        $stripped = substr($path, strlen($prefix));
        if ('' === $stripped) {
            $stripped = '/';
        }

        // Refuse to issue protocol-relative redirects. Request::getPathInfo()
        // does not collapse repeated slashes, so /<default>//evil.com would
        // otherwise produce a Location: //evil.com header (cross-origin).
        if (str_starts_with($stripped, '//')) {
            return;
        }

        $queryString = $request->getQueryString();
        $target = null !== $queryString ? $stripped.'?'.$queryString : $stripped;

        $requestEvent->setResponse(new RedirectResponse($target, Response::HTTP_MOVED_PERMANENTLY));
    }
}
