<?php

declare(strict_types=1);

namespace NotACms\Local\Service;

use NotACms\Service\TurnstileValidatorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * Decorator that skips Turnstile validation entirely.
 *
 * Useful for local development or trusted environments where you don't want to
 * configure a Cloudflare Turnstile secret key.
 */
#[AsDecorator(decorates: TurnstileValidatorInterface::class)]
final readonly class TurnstileSkipDecorator implements TurnstileValidatorInterface
{
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        return true;
    }
}
