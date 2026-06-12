<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DraftPreviewService implements DraftPreviewServiceInterface
{
    public function __construct(
        #[Autowire(service: 'app.session_toggle.draft')]
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
