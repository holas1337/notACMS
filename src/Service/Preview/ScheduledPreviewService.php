<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ScheduledPreviewService implements ScheduledPreviewServiceInterface
{
    public function __construct(
        #[Autowire(service: 'app.session_toggle.scheduled')]
        private SessionToggleServiceInterface $sessionToggleService,
    ) {
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
