<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ScheduledPreviewControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testToggleRedirectsToSafeReferer(): void
    {
        $this->client->request('GET', '/dev/scheduled/toggle', [], [], ['HTTP_REFERER' => '/blog/']);

        self::assertResponseRedirects('/blog/');
    }

    public function testToggleRejectsProtocolRelativeReferer(): void
    {
        $this->client->request('GET', '/dev/scheduled/toggle', [], [], ['HTTP_REFERER' => '//evil.example.com/path']);

        self::assertResponseRedirects('/');
    }

    public function testToggleRejectsAbsoluteReferer(): void
    {
        $this->client->request('GET', '/dev/scheduled/toggle', [], [], ['HTTP_REFERER' => 'http://evil.example.com/']);

        self::assertResponseRedirects('/');
    }

    public function testToggleWithoutRefererRedirectsHome(): void
    {
        $this->client->request('GET', '/dev/scheduled/toggle');

        self::assertResponseRedirects('/');
    }
}
