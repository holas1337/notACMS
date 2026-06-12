<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

interface ScheduledPreviewServiceInterface extends SessionToggleServiceInterface
{
    public const string SESSION_KEY = 'scheduled_preview';
}
