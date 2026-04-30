<?php

declare(strict_types=1);

namespace NotACms\Content;

use NotACms\Content\ValueObject\AdjacentPosts;
use NotACms\Content\ValueObject\ArchiveMonth;
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

    /** @var ContentItem[]|null */
    private ?array $sortedPosts = null;

    public function __construct(
        private readonly bool $includeDrafts = false,
        private readonly bool $includeScheduled = false,
    ) {
    }

    public function addPost(ContentItem $contentItem): void
    {
        $this->posts[] = $contentItem;
        $this->sortedPosts = null;
        if ('' !== $contentItem->url() && '0' !== $contentItem->url()) {
            $this->urlMap[$contentItem->url()] = $contentItem;
        }

        if (null !== $contentItem->directoryKey() && !array_key_exists($contentItem->directoryKey(), $this->directoryKeyMap)) {
            $this->directoryKeyMap[$contentItem->directoryKey()] = $contentItem;
        }
    }

    public function addPage(ContentItem $contentItem): void
    {
        $this->pages[] = $contentItem;
        if ('' !== $contentItem->url() && '0' !== $contentItem->url()) {
            $this->urlMap[$contentItem->url()] = $contentItem;
        }

        if (null !== $contentItem->directoryKey() && !array_key_exists($contentItem->directoryKey(), $this->directoryKeyMap)) {
            $this->directoryKeyMap[$contentItem->directoryKey()] = $contentItem;
        }
    }

    public function findByUrl(string $url): ?ContentItem
    {
        return $this->urlMap[$url] ?? null;
    }

    public function findByDirectoryKey(string $directoryKey): ?ContentItem
    {
        return $this->directoryKeyMap[$directoryKey] ?? null;
    }

    /** @return ContentItem[] */
    public function getAllPosts(): array
    {
        if (null === $this->sortedPosts) {
            $posts = array_values(array_filter(
                $this->posts,
                fn (ContentItem $contentItem): bool => ($this->includeDrafts || !$contentItem->isDraft()) && ($this->includeScheduled || !$contentItem->isScheduled()),
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

    /** @return array<int, int> year => count, newest first */
    public function getArchiveYears(): array
    {
        $years = [];
        foreach ($this->getAllPosts() as $contentItem) {
            $date = $contentItem->date();
            if (null === $date) {
                continue;
            }

            $year = (int) $date->format('Y');
            $years[$year] = ($years[$year] ?? 0) + 1;
        }

        krsort($years);

        return $years;
    }

    /** @return ContentItem[] */
    public function getAllPages(): array
    {
        $pages = $this->pages;
        usort($pages, fn (ContentItem $a, ContentItem $b): int => $a->menuWeight() <=> $b->menuWeight());

        return $pages;
    }

    /** @return ContentItem[] */
    public function getStaticPages(): array
    {
        return array_values(array_filter(
            $this->pages,
            fn (ContentItem $contentItem): bool => !$contentItem->isDynamic(),
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

    public function getAdjacentPosts(ContentItem $contentItem): AdjacentPosts
    {
        $posts = array_values($this->getAllPosts());
        $currentIndex = array_find_key($posts, fn ($post): bool => $post->url() === $contentItem->url());

        if (null === $currentIndex) {
            return new AdjacentPosts(prev: null, next: null);
        }

        return new AdjacentPosts(
            prev: $currentIndex < count($posts) - 1 ? $posts[$currentIndex + 1] : null,
            next: 0 < $currentIndex ? $posts[$currentIndex - 1] : null,
        );
    }
}
