<?php

declare(strict_types=1);

namespace NotACms\Content;

use NotACms\Content\ValueObject\AdjacentPosts;
use NotACms\Content\ValueObject\ArchiveMonth;
use NotACms\Content\ValueObject\ArchiveYearData;
use NotACms\Content\ValueObject\CategoryCount;
use NotACms\Content\ValueObject\TagCount;

final class ContentTree
{
    /** @var ContentItem[] */
    private array $posts = [];

    /** @var ContentItem[] */
    private array $pages = [];

    /** @var array<string, ContentItem> */
    private array $urlMap = [];

    /** @var array<string, ContentItem> */
    private array $directoryKeyMap = [];

    /** @var array<string, string|false> basename → full directory key, false when ambiguous */
    private array $directoryKeyBasenames = [];

    /** @var ContentItem[]|null */
    private ?array $sortedPosts = null;

    /** @var list<string> */
    private array $warnings = [];

    public function __construct(
        private readonly bool $includeDrafts = false,
        private readonly bool $includeScheduled = false,
    ) {
    }

    public function addPost(ContentItem $contentItem): void
    {
        $this->posts[] = $contentItem;
        $this->sortedPosts = null;
        $this->registerInMaps($contentItem);
    }

    public function addPage(ContentItem $contentItem): void
    {
        $this->pages[] = $contentItem;
        $this->registerInMaps($contentItem);
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /** @return list<string> */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    private function registerInMaps(ContentItem $contentItem): void
    {
        if (!$this->isVisible($contentItem)) {
            return;
        }

        $url = $contentItem->url();
        if ('' !== $url && '0' !== $url) {
            $existing = $this->urlMap[$url] ?? null;
            if ($existing instanceof ContentItem && $existing->sourcePath !== $contentItem->sourcePath) {
                $this->addWarning(sprintf(
                    'Duplicate URL %s: %s overwritten by %s',
                    $url,
                    $existing->sourcePath ?? '?',
                    $contentItem->sourcePath ?? '?',
                ));
            }

            $this->urlMap[$url] = $contentItem;
        }

        $this->registerDirectoryKey($contentItem);
    }

    private function registerDirectoryKey(ContentItem $contentItem): void
    {
        $directoryKey = $contentItem->directoryKey();
        if (null === $directoryKey) {
            return;
        }

        if (!array_key_exists($directoryKey, $this->directoryKeyMap)) {
            $this->directoryKeyMap[$directoryKey] = $contentItem;
        }

        $basename = basename($directoryKey);
        if ($basename === $directoryKey) {
            return;
        }

        if (!array_key_exists($basename, $this->directoryKeyBasenames)) {
            $this->directoryKeyBasenames[$basename] = $directoryKey;

            return;
        }

        if (false !== $this->directoryKeyBasenames[$basename] && $this->directoryKeyBasenames[$basename] !== $directoryKey) {
            $this->addWarning(sprintf(
                'Ambiguous directory key "%s" (%s vs %s) — use the full path in content_item()/content_url()',
                $basename,
                $this->directoryKeyBasenames[$basename],
                $directoryKey,
            ));
            $this->directoryKeyBasenames[$basename] = false;
        }
    }

    private function isVisible(ContentItem $contentItem): bool
    {
        return ($this->includeDrafts || !$contentItem->isDraft())
            && ($this->includeScheduled || !$contentItem->isScheduled());
    }

    public function findByUrl(string $url): ?ContentItem
    {
        return $this->urlMap[$url] ?? null;
    }

    public function findByDirectoryKey(string $directoryKey): ?ContentItem
    {
        if (isset($this->directoryKeyMap[$directoryKey])) {
            return $this->directoryKeyMap[$directoryKey];
        }

        $fullKey = $this->directoryKeyBasenames[$directoryKey] ?? null;

        return is_string($fullKey) ? ($this->directoryKeyMap[$fullKey] ?? null) : null;
    }

    /** @return ContentItem[] */
    public function getAllPosts(): array
    {
        if (null === $this->sortedPosts) {
            $posts = array_values(array_filter(
                $this->posts,
                $this->isVisible(...),
            ));
            usort($posts, function (ContentItem $a, ContentItem $b): int {
                if ($a->isPinned() !== $b->isPinned()) {
                    return $a->isPinned() ? -1 : 1;
                }

                return $b->date() <=> $a->date();
            });
            $this->sortedPosts = $posts;
        }

        return $this->sortedPosts;
    }

    /** @return ContentItem[] */
    public function getPostsByCategory(string $category): array
    {
        return array_values(array_filter(
            $this->getAllPosts(),
            fn (ContentItem $contentItem): bool => $contentItem->category() === $category,
        ));
    }

    /** @return ContentItem[] */
    public function getPostsByTag(string $tag): array
    {
        return array_values(array_filter(
            $this->getAllPosts(),
            fn (ContentItem $contentItem): bool => in_array($tag, $contentItem->tags(), true),
        ));
    }

    /** @return ContentItem[] */
    public function getPostsByYearMonth(int $year, int $month): array
    {
        return array_values(array_filter(
            $this->getAllPosts(),
            function (ContentItem $contentItem) use ($year, $month): bool {
                $date = $contentItem->date();

                return $date instanceof \DateTimeImmutable && (int) $date->format('Y') === $year && (int) $date->format('m') === $month;
            },
        ));
    }

    public function findPostBySlug(string $slug): ?ContentItem
    {
        return $this->findBySlug($slug, $this->getAllPosts());
    }

    /** @return ContentItem[] */
    public function getScheduledPosts(): array
    {
        return array_values(array_filter(
            $this->posts,
            fn (ContentItem $contentItem): bool => !$contentItem->isDraft() && $contentItem->isScheduled(),
        ));
    }

    public function findScheduledPostBySlug(string $slug): ?ContentItem
    {
        return $this->findBySlug($slug, $this->getScheduledPosts());
    }

    /**
     * @param ContentItem[] $posts
     */
    private function findBySlug(string $slug, array $posts): ?ContentItem
    {
        foreach ($posts as $post) {
            if ($post->slug() === $slug) {
                return $post;
            }
        }

        return null;
    }

    /** @return TagCount[] */
    public function getAllTags(): array
    {
        $tags = [];
        foreach ($this->getAllPosts() as $contentItem) {
            foreach ($contentItem->tags() as $tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
        }

        uksort($tags, static fn (string $a, string $b): int => [$tags[$b], $a] <=> [$tags[$a], $b]);

        return array_map(
            fn (string $tag, int $count): TagCount => new TagCount($tag, $count),
            array_keys($tags),
            array_values($tags),
        );
    }

    /** @return CategoryCount[] */
    public function getAllCategories(): array
    {
        $categories = [];
        foreach ($this->getAllPosts() as $contentItem) {
            $category = $contentItem->category();
            if ($category) {
                $categories[$category] = ($categories[$category] ?? 0) + 1;
            }
        }

        return array_map(
            fn (string $category, int $count): CategoryCount => new CategoryCount($category, $count),
            array_keys($categories),
            array_values($categories),
        );
    }

    /** @return ArchiveMonth[] */
    public function getArchiveMonths(): array
    {
        $months = [];
        foreach ($this->getAllPosts() as $contentItem) {
            $date = $contentItem->date();
            if (null === $date) {
                continue;
            }

            $key = $date->format('Y-m');
            $months[$key] = ($months[$key] ?? 0) + 1;
        }

        krsort($months);

        return array_map(function (string $key, int $count): ArchiveMonth {
            [$year, $month] = explode('-', $key);

            return new ArchiveMonth((int) $year, (int) $month, $count);
        }, array_keys($months), array_values($months));
    }

    /** @return ContentItem[] */
    public function getPostsByYear(int $year): array
    {
        return array_values(array_filter(
            $this->getAllPosts(),
            static fn (ContentItem $contentItem): bool => $contentItem->date() instanceof \DateTimeImmutable && (int) $contentItem->date()->format('Y') === $year,
        ));
    }

    /** @return ArchiveYearData[] newest first */
    public function getArchiveYears(): array
    {
        $counts = [];
        foreach ($this->getAllPosts() as $contentItem) {
            $date = $contentItem->date();
            if (null === $date) {
                continue;
            }

            $year = (int) $date->format('Y');
            $counts[$year] = ($counts[$year] ?? 0) + 1;
        }

        krsort($counts);

        return array_map(
            fn (int $year, int $count): ArchiveYearData => new ArchiveYearData($year, $count),
            array_keys($counts),
            $counts,
        );
    }

    /** @return ContentItem[] */
    public function getAllPages(): array
    {
        $pages = array_values(array_filter(
            $this->pages,
            $this->isVisible(...),
        ));
        usort($pages, fn (ContentItem $a, ContentItem $b): int => $a->menuWeight() <=> $b->menuWeight());

        return $pages;
    }

    /** @return ContentItem[] */
    public function getStaticPages(): array
    {
        return array_values(array_filter(
            $this->pages,
            fn (ContentItem $contentItem): bool => $this->isVisible($contentItem) && !$contentItem->isDynamic(),
        ));
    }

    /** @return ContentItem[] non-dynamic, visible pages with a real URL, excluding the home page */
    public function getPublishableStaticPages(string $homeUrl): array
    {
        return array_values(array_filter(
            $this->getStaticPages(),
            fn (ContentItem $contentItem): bool => '' !== $contentItem->url() && $contentItem->url() !== $homeUrl,
        ));
    }

    /** @return ContentItem[] */
    public function getSeriesPosts(string $seriesKey): array
    {
        $posts = array_filter(
            $this->getAllPosts(),
            static fn (ContentItem $contentItem): bool => $contentItem->series() === $seriesKey,
        );

        usort($posts, static fn (ContentItem $a, ContentItem $b): int => ($a->seriesOrder() ?? 0) <=> ($b->seriesOrder() ?? 0));

        return $posts;
    }

    /** @return ContentItem[] */
    public function getAllItems(): array
    {
        return array_merge($this->posts, $this->pages);
    }

    public function getSeriesPosition(ContentItem $contentItem): int
    {
        $series = $contentItem->series();
        if (null === $series) {
            return 1;
        }

        foreach ($this->getSeriesPosts($series) as $index => $seriesPost) {
            if ($seriesPost->isSame($contentItem)) {
                return $index + 1;
            }
        }

        return 1;
    }

    public function getAdjacentPosts(ContentItem $contentItem): AdjacentPosts
    {
        $posts = array_values($this->getAllPosts());
        $currentIndex = array_find_key($posts, fn (ContentItem $post): bool => $post->isSame($contentItem));

        if (null === $currentIndex) {
            return new AdjacentPosts(prev: null, next: null);
        }

        return new AdjacentPosts(
            prev: $currentIndex < count($posts) - 1 ? $posts[$currentIndex + 1] : null,
            next: 0 < $currentIndex ? $posts[$currentIndex - 1] : null,
        );
    }
}
