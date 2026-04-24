<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Controller;

use NotACms\Service\TurnstileValidatorInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\MailerInterface;

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

    public function testApiContactReturns422WhenTurnstileVerificationFails(): void
    {
        $turnstile = $this->createStub(TurnstileValidatorInterface::class);
        $turnstile->method('verify')->willReturn(false);
        self::getContainer()->set(TurnstileValidatorInterface::class, $turnstile);

        $this->client->request('POST', '/api/contact', [
            'contact' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => 'Test subject line',
                'message' => 'A message long enough to pass validation constraints.',
                'turnstile_token' => 'fake-token',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testApiContactReturns500WhenMailerThrows(): void
    {
        $turnstile = $this->createStub(TurnstileValidatorInterface::class);
        $turnstile->method('verify')->willReturn(true);
        self::getContainer()->set(TurnstileValidatorInterface::class, $turnstile);

        $mailer = $this->createStub(MailerInterface::class);
        $mailer->method('send')->willThrowException(new \RuntimeException('SMTP down'));
        self::getContainer()->set(MailerInterface::class, $mailer);

        $this->client->request('POST', '/api/contact', [
            'contact' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'subject' => 'Test subject line',
                'message' => 'A message long enough to pass validation constraints.',
                'turnstile_token' => 'fake-token',
            ],
        ]);

        self::assertResponseStatusCodeSame(500);
    }
}
