<?php

declare(strict_types=1);

namespace NotACms\Service;

interface TurnstileValidatorInterface
{
    public const string VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public const array TEST_KEY_PREFIXES = ['1x0000', '2x0000', '3x0000'];

    public function verify(string $token, ?string $remoteIp = null): bool;
}
