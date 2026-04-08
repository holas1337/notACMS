<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service;

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
        $this->validator = new TurnstileValidator($this->httpClient, 'test-secret', new NullLogger());
    }

    public function testReturnsTrueOnSuccess(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['success' => true]);
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->validator->verify('valid-token');

        self::assertTrue($result);
    }

    public function testReturnsFalseOnFailure(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['success' => false]);
        $this->httpClient->method('request')->willReturn($response);

        $result = $this->validator->verify('invalid-token');

        self::assertFalse($result);
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

    public function testIncludesRemoteIpWhenProvided(): void
    {
        $capturedBody = null;
        $response = $this->createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['success' => true]);

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
        $response->method('toArray')->willReturn(['success' => true]);

        $this->httpClient->method('request')
            ->willReturnCallback(function (string $method, string $url, array $options) use (&$capturedBody, $response): ResponseInterface {
                $capturedBody = $options['body'];

                return $response;
            });

        $this->validator->verify('token');

        self::assertArrayNotHasKey('remoteip', $capturedBody);
    }
}
