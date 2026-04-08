<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

interface DraftPreviewServiceInterface
{
    public const SESSION_KEY = 'draft_preview';

    public function isEnabled(): bool;

    public function toggle(): void;
}
