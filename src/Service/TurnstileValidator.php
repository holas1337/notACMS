<?php

declare(strict_types=1);

namespace NotACms\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class TurnstileValidator implements TurnstileValidatorInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%env(TURNSTILE_SECRET_KEY)%')]
        private string $secretKey,
        #[Autowire(service: 'monolog.logger.contact')]
        private LoggerInterface $logger,
    ) {
    }

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        if ('' === $token) {
            $this->logger->warning('Turnstile token is empty');

            return false;
        }

        try {
            $body = [
                'secret' => $this->secretKey,
                'response' => $token,
            ];
            if (null !== $remoteIp) {
                $body['remoteip'] = $remoteIp;
            }

            $response = $this->httpClient->request('POST', self::VERIFY_URL, [
                'body' => $body,
            ]);

            $data = $response->toArray();

            return (bool) ($data['success'] ?? false);
        } catch (\Throwable $throwable) {
            $this->logger->error('Turnstile verification failed', [
                'exception' => $throwable,
                'token_length' => strlen($token),
            ]);

            return false;
        }
    }
}
