<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class ParsedMarkdown
{
    /**
     * @param array<string, mixed> $frontMatter
     */
    public function __construct(
        public array $frontMatter,
        public string $html,
    ) {
    }
}
