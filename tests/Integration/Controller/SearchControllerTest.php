<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SearchControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSearchPageReturns200(): void
    {
        $this->client->request('GET', '/search/');

        self::assertResponseIsSuccessful();
    }

    public function testSearchPageIsNoindex(): void
    {
        $crawler = $this->client->request('GET', '/search/');

        $meta = $crawler->filterXpath('//meta[@name="robots"]/@content');
        self::assertNotEmpty($meta);
        self::assertStringContainsString('noindex', $meta->text());
    }
}
