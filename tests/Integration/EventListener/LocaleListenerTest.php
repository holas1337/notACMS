<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\EventListener;

use NotACms\EventListener\LocaleListener;
use NotACms\Service\SiteConfigServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

final class LocaleListenerTest extends TestCase
{
    private LocaleAwareInterface $localeAware;

    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->localeAware = $this->createStub(LocaleAwareInterface::class);
        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getLocales')->willReturn(['en', 'pl']);
        $this->siteConfigService->method('getDefaultLocale')->willReturn('en');
    }

    private function createEvent(string $path): RequestEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create($path);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    public function testSetsLocaleFromPath(): void
    {
        $this->siteConfigService->method('detectLocaleFromPath')
            ->willReturn('pl');

        $listener = new LocaleListener($this->localeAware, $this->siteConfigService);
        $event = $this->createEvent('/pl/wpisy/');

        $listener($event);

        self::assertSame('pl', $event->getRequest()->getLocale());
    }

    public function testSetsDefaultLocaleForRootPath(): void
    {
        $this->siteConfigService->method('detectLocaleFromPath')
            ->willReturn('en');

        $listener = new LocaleListener($this->localeAware, $this->siteConfigService);
        $event = $this->createEvent('/');

        $listener($event);

        self::assertSame('en', $event->getRequest()->getLocale());
    }

    public function testUpdatesLocaleAware(): void
    {
        $this->siteConfigService->method('detectLocaleFromPath')
            ->willReturn('pl');
        $localeAware = $this->createMock(LocaleAwareInterface::class);
        $localeAware->expects(self::once())
            ->method('setLocale')
            ->with('pl');

        $listener = new LocaleListener($localeAware, $this->siteConfigService);
        $event = $this->createEvent('/pl/wpisy/');

        $listener($event);

        self::assertSame('pl', $event->getRequest()->getLocale());
    }
}
