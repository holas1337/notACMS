<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

interface ScheduledPreviewServiceInterface
{
    public const SESSION_KEY = 'scheduled_preview';

    public function isEnabled(): bool;

    public function toggle(): void;
}
