<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service;

use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Service\TurnstileValidator;
use NotACms\Service\TurnstileValidatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class TurnstileValidatorTest extends TestCase
{
    private TurnstileValidatorInterface $validator;

    private HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createStub(HttpClientInterface::class);
        $this->validator = $this->createValidator(baseUrl: 'https://example.com', debug: false);
    }

    private function createValidator(string $baseUrl, bool $debug): TurnstileValidatorInterface
    {
        $siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $siteConfigService->method('getBaseUrl')->willReturn($baseUrl);

        return new TurnstileValidator($this->httpClient, 'test-secret', new NullLogger(), $siteConfigService, $debug);
    }

    private function stubResponse(array $payload): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn($payload);
        $this->httpClient->method('request')->willReturn($response);
    }

    public function testReturnsFalseOnFailure(): void
    {
        $this->stubResponse(['success' => false]);

        self::assertFalse($this->validator->verify('invalid-token'));
    }

    public function testReturnsFalseWhenHostnameMissingInProduction(): void
    {
        $this->stubResponse(['success' => true]);

        self::assertFalse($this->validator->verify('valid-token'));
    }

    public function testReturnsFalseOnNetworkError(): void
    {
        $this->httpClient->method('request')
            ->willThrowException(new \RuntimeException('Connection refused'));

        $result = $this->validator->verify('some-token');

        self::assertFalse($result);
    }

    public function testReturnsFalseOnEmptyToken(): void
    {
        $result = $this->validator->verify('');

        self::assertFalse($result);
    }

    public function testReturnsTrueWhenHostnameMatchesBaseUrl(): void
    {
        $this->stubResponse(['success' => true, 'hostname' => 'example.com']);

        self::assertTrue($this->validator->verify('valid-token'));
    }

    public function testReturnsTrueForWwwVariantOfBaseUrlHost(): void
    {
        $this->stubResponse(['success' => true, 'hostname' => 'www.example.com']);

        self::assertTrue($this->validator->verify('valid-token'));
    }

    public function testReturnsFalseOnHostnameMismatch(): void
    {
        $this->stubResponse(['success' => true, 'hostname' => 'evil.com']);

        self::assertFalse($this->validator->verify('valid-token'));
    }

    public function testSkipsHostnameCheckInDebugMode(): void
    {
        $this->stubResponse(['success' => true, 'hostname' => 'evil.com']);
        $validator = $this->createValidator(baseUrl: 'https://example.com', debug: true);

        self::assertTrue($validator->verify('valid-token'));
    }

    public function testSkipsHostnameCheckWhenBaseUrlIsEmpty(): void
    {
        $this->stubResponse(['success' => true, 'hostname' => 'anything.com']);
        $validator = $this->createValidator(baseUrl: '', debug: false);

        self::assertTrue($validator->verify('valid-token'));
    }

    public function testSkipsHostnameCheckWhenBaseUrlIsMalformed(): void
    {
        $this->stubResponse(['success' => true, 'hostname' => 'anything.com']);
        $validator = $this->createValidator(baseUrl: '://invalid', debug: false);

        self::assertTrue($validator->verify('valid-token'));
    }

    public function testIncludesRemoteIpWhenProvided(): void
    {
        $capturedBody = null;
        $response = $this->createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['success' => true, 'hostname' => 'example.com']);

        $this->httpClient->method('request')
            ->willReturnCallback(function (string $method, string $url, array $options) use (&$capturedBody, $response): ResponseInterface {
                $capturedBody = $options['body'];

                return $response;
            });

        $this->validator->verify('token', '192.168.1.1');

        self::assertArrayHasKey('remoteip', $capturedBody);
        self::assertSame('192.168.1.1', $capturedBody['remoteip']);
    }

    public function testExcludesRemoteIpWhenNull(): void
    {
        $capturedBody = null;
        $response = $this->createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['success' => true, 'hostname' => 'example.com']);

        $this->httpClient->method('request')
            ->willReturnCallback(function (string $method, string $url, array $options) use (&$capturedBody, $response): ResponseInterface {
                $capturedBody = $options['body'];

                return $response;
            });

        $this->validator->verify('token');

        self::assertArrayNotHasKey('remoteip', $capturedBody);
    }
}
