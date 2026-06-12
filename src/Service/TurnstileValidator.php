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
        private SiteSettingsInterface $siteSettings,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {
    }

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $this->warnOnTestKeysInProduction();

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

            if (true !== ($data['success'] ?? false)) {
                return false;
            }

            return $this->isExpectedHostname($data['hostname'] ?? null);
        } catch (\Throwable $throwable) {
            $this->logger->error('Turnstile verification failed', [
                'exception' => $throwable,
                'token_length' => strlen($token),
            ]);

            return false;
        }
    }

    private function warnOnTestKeysInProduction(): void
    {
        if ($this->debug) {
            return;
        }

        foreach (self::TEST_KEY_PREFIXES as $testKeyPrefix) {
            if (str_starts_with($this->secretKey, $testKeyPrefix)) {
                $this->logger->error('Turnstile test keys are active in production — the CAPTCHA accepts every submission; set real keys in .env.local');

                return;
            }
        }
    }

    private function isExpectedHostname(mixed $hostname): bool
    {
        if ($this->debug) {
            return true;
        }

        if (!is_string($hostname) || '' === $hostname) {
            $this->logger->warning('Turnstile response missing hostname');

            return false;
        }

        $parsedHost = parse_url($this->siteSettings->getBaseUrl(), PHP_URL_HOST);
        if (false === $parsedHost) {
            $this->logger->warning('Turnstile base_url is malformed; hostname check skipped');

            return true;
        }

        $expectedHost = strtolower((string) $parsedHost);

        if ('' === $expectedHost) {
            return true;
        }

        $stripWww = static fn (string $h): string => preg_replace('/^www\./', '', $h) ?? $h;
        if ($stripWww($expectedHost) === $stripWww(strtolower($hostname))) {
            return true;
        }

        $this->logger->warning('Turnstile hostname mismatch', [
            'hostname' => $hostname,
            'expected' => $expectedHost,
        ]);

        return false;
    }
}
