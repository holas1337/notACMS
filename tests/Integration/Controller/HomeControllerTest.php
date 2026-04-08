<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\SidebarDataProviderInterface;
use NotACms\Service\Content\TagTranslationServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class HomeControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testHomePageReturns200(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    public function testHomePageContainsSiteName(): void
    {
        $crawler = $this->client->request('GET', '/');

        $meta = $crawler->filterXpath('//meta[@property="og:site_name"]/@content');
        self::assertGreaterThan(0, $meta->count());
        self::assertNotEmpty($meta->text());
    }

    public function testPolishHomePageReturns200(): void
    {
        $this->client->request('GET', '/pl/');

        self::assertResponseIsSuccessful();
    }
}
