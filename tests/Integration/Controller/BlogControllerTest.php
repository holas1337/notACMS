<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BlogControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testBlogListReturns200(): void
    {
        $this->client->request('GET', '/blog/');

        self::assertResponseIsSuccessful();
    }

    public function testBlogCategoryReturns200(): void
    {
        $this->client->request('GET', '/blog/tutorials/');

        self::assertResponseIsSuccessful();
    }

    public function testFeedReturnsRssContentType(): void
    {
        $this->client->request('GET', '/feed/');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('application/rss+xml', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testPolishBlogListReturns200(): void
    {
        $this->client->request('GET', '/pl/wpisy/');

        self::assertResponseIsSuccessful();
    }

    public function testNonexistentCategoryReturns404(): void
    {
        $this->client->request('GET', '/blog/nonexistent-category-xyz/');

        self::assertResponseStatusCodeSame(404);
    }

    public function testNonexistentTagReturns404(): void
    {
        $this->client->request('GET', '/tag/nonexistent-tag-xyz/');

        self::assertResponseStatusCodeSame(404);
    }

    public function testArchiveMonthReturns200(): void
    {
        $this->client->request('GET', '/archive/2026/04/');

        self::assertResponseIsSuccessful();
    }

    public function testArchiveYearReturns200(): void
    {
        $this->client->request('GET', '/archive/2026/');

        self::assertResponseIsSuccessful();
    }

    public function testBlogPaginationClampsToLastPage(): void
    {
        $this->client->request('GET', '/blog/page/999/');

        self::assertResponseIsSuccessful();
    }

    public function testBlogPaginationClampsToPage1(): void
    {
        $this->client->request('GET', '/blog/page/0/');

        self::assertResponseIsSuccessful();
    }

    public function testPolishArchiveReturns200(): void
    {
        $this->client->request('GET', '/pl/archiwum/2026/04/');

        self::assertResponseIsSuccessful();
    }
}
