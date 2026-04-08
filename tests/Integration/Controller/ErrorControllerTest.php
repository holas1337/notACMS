<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ErrorControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testNotFoundPageReturns404(): void
    {
        $this->client->request('GET', '/this-page-does-not-exist/');

        self::assertResponseStatusCodeSame(404);
    }

    public function testNotFoundPageContainsErrorMessage(): void
    {
        $this->client->request('GET', '/this-page-does-not-exist/');

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('404', $content);
    }

    public function testErrorPageReturns404ForDirectAccess(): void
    {
        $this->client->request('GET', '/404/');

        self::assertResponseStatusCodeSame(404);
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('404', $content);
    }

    public function testServerErrorPageReturns500ForDirectAccess(): void
    {
        $this->client->request('GET', '/500/');

        self::assertResponseStatusCodeSame(500);
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('500', $content);
    }

    public function testPolishNotFoundReturns404(): void
    {
        $this->client->request('GET', '/pl/nie-istnieje/');

        self::assertResponseStatusCodeSame(404);
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('404', $content);
    }
}
