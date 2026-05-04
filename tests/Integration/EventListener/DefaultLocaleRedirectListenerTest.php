<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\EventListener;

use NotACms\EventListener\DefaultLocaleRedirectListener;
use NotACms\Service\SiteConfigServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class DefaultLocaleRedirectListenerTest extends TestCase
{
    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getDefaultLocale')->willReturn('en');
    }

    private function createEvent(string $url, bool $mainRequest = true): RequestEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create($url);
        $type = $mainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST;

        return new RequestEvent($kernel, $request, $type);
    }

    private function listener(): DefaultLocaleRedirectListener
    {
        return new DefaultLocaleRedirectListener($this->siteConfigService);
    }

    public function testRedirectsExactDefaultLocale(): void
    {
        $event = $this->createEvent('https://example.com/en');
        $this->listener()($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(301, $event->getResponse()->getStatusCode());
        self::assertSame('/', $event->getResponse()->headers->get('Location'));
    }

    public function testRedirectsDefaultLocaleWithTrailingSlash(): void
    {
        $event = $this->createEvent('https://example.com/en/');
        $this->listener()($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(301, $event->getResponse()->getStatusCode());
        self::assertSame('/', $event->getResponse()->headers->get('Location'));
    }

    public function testRedirectsDefaultLocalePrefixedPath(): void
    {
        $event = $this->createEvent('https://example.com/en/blog/');
        $this->listener()($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(301, $event->getResponse()->getStatusCode());
        self::assertSame('/blog/', $event->getResponse()->headers->get('Location'));
    }

    public function testRedirectsPreservesQueryString(): void
    {
        $event = $this->createEvent('https://example.com/en/foo?x=1');
        $this->listener()($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(301, $event->getResponse()->getStatusCode());
        self::assertSame('/foo?x=1', $event->getResponse()->headers->get('Location'));
    }

    public function testDoesNotRedirectNonDefaultLocale(): void
    {
        $event = $this->createEvent('https://example.com/pl/');
        $this->listener()($event);

        self::assertNull($event->getResponse());
    }

    public function testDoesNotRedirectPathWithLocaleAsPrefix(): void
    {
        $event = $this->createEvent('https://example.com/english-thing/');
        $this->listener()($event);

        self::assertNull($event->getResponse());
    }

    public function testDoesNotRedirectRootPath(): void
    {
        $event = $this->createEvent('https://example.com/');
        $this->listener()($event);

        self::assertNull($event->getResponse());
    }

    public function testDoesNotRedirectSubRequest(): void
    {
        $event = $this->createEvent('https://example.com/en', false);
        $this->listener()($event);

        self::assertNull($event->getResponse());
    }

    public function testDoesNotIssueProtocolRelativeRedirect(): void
    {
        // Request::getPathInfo() does not collapse repeated slashes, so /en//evil.com
        // strips to //evil.com — a protocol-relative redirect that browsers would
        // follow cross-origin. The listener must refuse to issue such a redirect.
        $event = $this->createEvent('https://example.com/en//evil.com');
        $this->listener()($event);

        self::assertNull($event->getResponse(), 'Listener must not issue a redirect for paths that strip to a protocol-relative target.');
    }
}
