<?php

declare(strict_types=1);

namespace NotACms\Service;

interface TurnstileValidatorInterface
{
    public const string VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function verify(string $token, ?string $remoteIp = null): bool;
}
