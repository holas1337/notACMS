<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class BlogPostingData
{
    /**
     * @param array<int, string>        $keywords
     * @param array<string, mixed>|null $image
     * @param array<string, mixed>|null $author
     * @param array<string, mixed>|null $publisher
     */
    public function __construct(
        public string $headline,
        public string $url,
        public string $inLanguage,
        public string $datePublished = '',
        public ?string $dateModified = null,
        public ?string $description = null,
        public int $wordCount = 0,
        public ?string $articleSection = null,
        public array $keywords = [],
        public ?array $image = null,
        public ?array $author = null,
        public ?array $publisher = null,
        public ?string $mainEntityOfPage = null,
    ) {
    }
}
