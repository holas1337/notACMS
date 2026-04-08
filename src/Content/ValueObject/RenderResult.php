<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class RenderResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public int $pages,
        public int $skipped,
        public array $errors,
    ) {
    }
}
