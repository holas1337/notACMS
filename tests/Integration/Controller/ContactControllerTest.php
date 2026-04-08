<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testContactPageReturns200(): void
    {
        $this->client->request('GET', '/contact/');

        self::assertResponseIsSuccessful();
    }

    public function testContactPageContainsForm(): void
    {
        $this->client->request('GET', '/contact/');

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('contact-form', $content);
    }

    public function testApiContactReturns422ForInvalidData(): void
    {
        $this->client->request('POST', '/api/contact', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        self::assertResponseStatusCodeSame(422);
    }

    public function testApiContactReturns422ForEmptyBody(): void
    {
        $this->client->request('POST', '/api/contact', [], [], ['CONTENT_TYPE' => 'application/json'], '');

        self::assertResponseStatusCodeSame(422);
    }

    public function testPolishContactPageReturns200(): void
    {
        $this->client->request('GET', '/pl/kontakt/');

        self::assertResponseIsSuccessful();
    }

    public function testApiContactReturns422ForEmptyTurnstileToken(): void
    {
        $this->client->request('POST', '/api/contact', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'contact' => [
                'name' => 'Test',
                'email' => 'test@example.com',
                'subject' => 'Test Subject',
                'message' => 'This is a test message with enough content',
                'turnstile_token' => '',
            ],
        ]));

        self::assertResponseStatusCodeSame(422);
    }
}
