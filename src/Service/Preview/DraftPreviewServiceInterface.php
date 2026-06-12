<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

interface DraftPreviewServiceInterface extends SessionToggleServiceInterface
{
    public const string SESSION_KEY = 'draft_preview';
}
