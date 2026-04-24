<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class SessionToggleService implements SessionToggleServiceInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private string $sessionKey,
    ) {
    }

    public function isEnabled(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request || !$request->hasSession()) {
            return false;
        }

        return (bool) $request->getSession()->get($this->sessionKey, false);
    }

    public function toggle(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request || !$request->hasSession()) {
            return;
        }

        $request->getSession()->set($this->sessionKey, !$this->isEnabled());
    }
}
