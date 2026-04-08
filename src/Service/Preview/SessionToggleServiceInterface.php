<?php

declare(strict_types=1);

namespace NotACms\Service\Preview;

interface SessionToggleServiceInterface
{
    public function isEnabled(): bool;

    public function toggle(): void;
}
