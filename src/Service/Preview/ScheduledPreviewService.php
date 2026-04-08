<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

final readonly class ScheduledPreviewService implements ScheduledPreviewServiceInterface
{
    public function __construct(private SessionToggleServiceInterface $sessionToggleService)
    {
    }

    public function isEnabled(): bool
    {
        return $this->sessionToggleService->isEnabled();
    }

    public function toggle(): void
    {
        $this->sessionToggleService->toggle();
    }
}
