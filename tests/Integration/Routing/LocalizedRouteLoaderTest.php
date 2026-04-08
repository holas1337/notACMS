<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Routing;

use NotACms\Routing\LocalizedRouteLoader;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\TmpDirTrait;
use PHPUnit\Framework\TestCase;

final class LocalizedRouteLoaderTest extends TestCase
{
    use TmpDirTrait;
    private string $tmpContentDir;

    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->tmpContentDir = sys_get_temp_dir() . '/notacms_routes_' . uniqid();
        mkdir($this->tmpContentDir, 0755, true);

        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getLocales')->willReturn(['en', 'pl']);
        $this->siteConfigService->method('getDefaultLocale')->willReturn('en');
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpContentDir);
    }

    private function createLoader(): LocalizedRouteLoader
    {
        return new LocalizedRouteLoader(
            $this->siteConfigService,
            dirname(__DIR__, 3),
            $this->tmpContentDir,
        );
    }

    public function testSupportsOnlyLocalizedType(): void
    {
        $loader = $this->createLoader();

        self::assertTrue($loader->supports(null, 'localized'));
        self::assertFalse($loader->supports(null, 'other'));
        self::assertFalse($loader->supports(null, null));
    }

    public function testLoadGeneratesRoutesForAllLocales(): void
    {
        $loader = $this->createLoader();
        $routes = $loader->load(null, 'localized');

        $routeNames = array_keys(iterator_to_array($routes->getIterator()));

        self::assertContains('home_en', $routeNames);
        self::assertContains('home_pl', $routeNames);
        self::assertContains('blog_list_en', $routeNames);
        self::assertContains('blog_list_pl', $routeNames);
    }

    public function testLoadSetsLocaleDefaultOnRoutes(): void
    {
        $loader = $this->createLoader();
        $routes = $loader->load(null, 'localized');

        $enRoute = $routes->get('home_en');
        $plRoute = $routes->get('home_pl');

        self::assertSame('en', $enRoute->getDefault('locale'));
        self::assertSame('pl', $plRoute->getDefault('locale'));
    }

    public function testLoadAppliesYamlOverrides(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/_routes.yaml',
            "routes:\n  blog_list:\n    pl: '/wpisy/'\n",
        );

        $loader = $this->createLoader();
        $routes = $loader->load(null, 'localized');

        $enRoute = $routes->get('blog_list_en');
        $plRoute = $routes->get('blog_list_pl');

        self::assertSame('/blog/', $enRoute->getPath());
        self::assertSame('/pl/wpisy/', $plRoute->getPath());
    }

    public function testLoadPrefixesNonOverriddenPathsForNonDefaultLocale(): void
    {
        $loader = $this->createLoader();
        $routes = $loader->load(null, 'localized');

        $enRoute = $routes->get('blog_list_en');
        $plRoute = $routes->get('blog_list_pl');

        self::assertSame('/blog/', $enRoute->getPath());
        self::assertSame('/pl/blog/', $plRoute->getPath());
    }

    public function testLoadThrowsWhenCalledTwice(): void
    {
        $loader = $this->createLoader();
        $loader->load(null, 'localized');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LocalizedRouteLoader is already loaded.');
        $loader->load(null, 'localized');
    }

    public function testLoadSetsMethodRestrictions(): void
    {
        $loader = $this->createLoader();
        $routes = $loader->load(null, 'localized');

        $apiEn = $routes->get('api_contact_en');
        $apiPl = $routes->get('api_contact_pl');

        self::assertSame(['POST'], $apiEn->getMethods());
        self::assertSame(['POST'], $apiPl->getMethods());
    }

    public function testLoadSetsRequirements(): void
    {
        $loader = $this->createLoader();
        $routes = $loader->load(null, 'localized');

        $paginated = $routes->get('blog_list_paginated_en');

        self::assertSame(['page' => '\d+'], $paginated->getRequirements());
    }
}
