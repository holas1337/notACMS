<?php

declare(strict_types=1);

namespace NotACms\Content;

final readonly class ContentItem
{
    private const int DEFAULT_WORDS_PER_MINUTE = 200;

    private const int DEFAULT_EXCERPT_LENGTH = 250;

    private const int DEFAULT_MENU_WEIGHT = 50;

    /**
     * @param array<string, mixed> $frontMatter
     */
    public function __construct(
        private array $frontMatter,
        public string $htmlContent,
        public string $locale,
        private string $url = '',
        public ?string $sourcePath = null,
        private bool $isIndexItem = false,
        private ?string $directoryKey = null,
    ) {
    }

    public function title(): string
    {
        return $this->frontMatter['title'] ?? '';
    }

    public function slug(): string
    {
        if (isset($this->frontMatter['slug'])) {
            $slug = $this->frontMatter['slug'];
            $parts = explode('/', rtrim($slug, '/'));

            return end($parts) ?: '';
        }

        return '';
    }

    public function date(): ?\DateTimeImmutable
    {
        if (!isset($this->frontMatter['date'])) {
            return null;
        }

        return $this->parseDate($this->frontMatter['date']);
    }

    public function updatedDate(): ?\DateTimeImmutable
    {
        if (!isset($this->frontMatter['updated'])) {
            return null;
        }

        return $this->parseDate($this->frontMatter['updated']);
    }

    public function description(): string
    {
        return $this->frontMatter['description'] ?? '';
    }

    /**
     * @return string[]
     */
    public function tags(): array
    {
        $tags = $this->frontMatter['tags'] ?? [];

        return is_array($tags) ? $tags : [];
    }

    /**
     * @return string[]
     */
    public function relatedSlugs(): array
    {
        $related = $this->frontMatter['related'] ?? [];

        return is_array($related) ? $related : [];
    }

    public function image(): ?string
    {
        return $this->frontMatter['image'] ?? null;
    }

    public function imageAlt(): string
    {
        return $this->frontMatter['image_alt'] ?? $this->title();
    }

    public function template(): string
    {
        return $this->frontMatter['template'] ?? 'page/default';
    }

    public function isDraft(): bool
    {
        return (bool) ($this->frontMatter['draft'] ?? false);
    }

    public function isScheduled(): bool
    {
        $date = $this->date();

        return $date instanceof \DateTimeImmutable && $date > new \DateTimeImmutable();
    }

    public function isPinned(): bool
    {
        $pinned = $this->frontMatter['pinned'] ?? false;

        if (!$pinned) {
            return false;
        }

        $until = $this->parseDate($pinned);

        if (!$until instanceof \DateTimeImmutable) {
            return false;
        }

        return $until > new \DateTimeImmutable('today');
    }

    public function isDynamic(): bool
    {
        return (bool) ($this->frontMatter['dynamic'] ?? false);
    }

    public function hasToc(): bool
    {
        return (bool) ($this->frontMatter['toc'] ?? false);
    }

    public function isFeatured(): bool
    {
        return true === ($this->frontMatter['featured'] ?? false);
    }

    public function series(): ?string
    {
        return $this->frontMatter['series'] ?? null;
    }

    public function seriesOrder(): ?int
    {
        $order = $this->frontMatter['series_order'] ?? null;

        return null !== $order ? (int) $order : null;
    }

    public function directoryKey(): ?string
    {
        return $this->directoryKey;
    }

    public function menuWeight(): int
    {
        return (int) ($this->frontMatter['menu']['weight'] ?? self::DEFAULT_MENU_WEIGHT);
    }

    public function isIndex(): bool
    {
        return $this->isIndexItem;
    }

    public function category(): ?string
    {
        return $this->frontMatter['category'] ?? null;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function readingTime(int $wordsPerMinute = self::DEFAULT_WORDS_PER_MINUTE): int
    {
        return max(1, (int) ceil($this->wordCount() / $wordsPerMinute));
    }

    public function wordCount(): int
    {
        $text = strip_tags($this->htmlContent);

        return str_word_count($text);
    }

    public function excerpt(int $length = self::DEFAULT_EXCERPT_LENGTH): string
    {
        $html = (string) preg_replace('/<a[^>]*class="heading-anchor"[^>]*>.*?<\/a>/s', '', $this->htmlContent);
        $html = str_replace(['</p>', '</li>', '</h1>', '</h2>', '</h3>', '</h4>', '<br>', '<br/>'], ' ', $html);

        $text = strip_tags($html);
        $text = (string) preg_replace('/\s+/', ' ', trim($text));
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'…';
    }

    private function parseDate(mixed $date): ?\DateTimeImmutable
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date;
        }

        if ($date instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($date);
        }

        if (is_int($date)) {
            return new \DateTimeImmutable('@'.$date);
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', (string) $date);

        return false !== $parsed ? $parsed->setTime(0, 0, 0) : null;
    }
}
