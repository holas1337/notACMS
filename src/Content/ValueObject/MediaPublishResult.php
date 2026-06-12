<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class MediaPublishResult
{
    /**
     * @param list<string> $warnings
     * @param list<string> $notes
     */
    public function __construct(
        public int $copiedDirectories,
        public int $optimized,
        public int $variants,
        public array $warnings,
        public array $notes,
    ) {
    }
}
