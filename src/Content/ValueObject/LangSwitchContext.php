<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class LangSwitchContext
{
    /**
     * @param array<string, string>|null $urlOverrides per-locale URL map (e.g. translated tag pages)
     */
    public function __construct(
        public ?array $urlOverrides = null,
        public ?string $filterType = null,
        public ?int $currentPage = null,
        public ?int $archiveYear = null,
        public ?int $archiveMonth = null,
    ) {
    }
}
