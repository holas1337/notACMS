<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MediaControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testServesFixtureFile(): void
    {
        $this->client->request('GET', '/media/test-post/test.webp');

        self::assertResponseIsSuccessful();
    }

    public function testReturns404ForMissingFile(): void
    {
        $this->client->request('GET', '/media/test-post/missing.webp');

        self::assertResponseStatusCodeSame(404);
    }

    public function testReturns404ForMissingDirKey(): void
    {
        $this->client->request('GET', '/media/nonexistent-dir-xyz/test.webp');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPathTraversalInDirKeyIsRejectedByRouteRegex(): void
    {
        $this->client->request('GET', '/media/..%2Fetc/passwd');

        self::assertResponseStatusCodeSame(404);
    }

    public function testPathTraversalInFilenameIsRejected(): void
    {
        $this->client->request('GET', '/media/test-post/..%2F..%2Fetc%2Fpasswd');

        self::assertResponseStatusCodeSame(404);
    }
}
