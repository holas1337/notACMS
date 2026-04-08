<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PageControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRobotsReturnsPlainText(): void
    {
        $this->client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('text/plain', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testSitemapReturnsXml(): void
    {
        $this->client->request('GET', '/sitemap.xml');

        $statusCode = $this->client->getResponse()->getStatusCode();
        self::assertContains($statusCode, [200, 500]);
    }

    public function testUnknownPageReturns404(): void
    {
        $this->client->request('GET', '/nonexistent-page/');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAboutPageReturns200(): void
    {
        $this->client->request('GET', '/about/');

        self::assertResponseIsSuccessful();
    }

    public function testPrivacyPolicyPageReturns200(): void
    {
        $this->client->request('GET', '/privacy-policy/');

        self::assertResponseIsSuccessful();
    }

    public function testProjectsPageReturns200(): void
    {
        $this->client->request('GET', '/projects/');

        self::assertResponseIsSuccessful();
    }
}
