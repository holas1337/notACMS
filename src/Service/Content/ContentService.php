<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentItem;
use NotACms\Content\ContentTree;
use NotACms\Service\LocaleConfigInterface;
use NotACms\Service\Preview\DraftPreviewServiceInterface;
use NotACms\Service\Preview\ScheduledPreviewServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ContentService implements ContentServiceInterface, ContentCacheInterface, ContentTreeProviderInterface
{
    /** @var array<string, ContentTree> */
    private array $trees = [];

    /** @var array<string, array<string, string>>|null */
    private ?array $translationMap = null;

    public function __construct(
        private readonly ContentTreeBuilderInterface $contentTreeBuilder,
        #[Autowire(service: 'app.content')]
        private readonly CacheInterface $cache,
        private readonly TranslationMapBuilderInterface $translationMapBuilder,
        private readonly DraftPreviewServiceInterface $draftPreviewService,
        private readonly ScheduledPreviewServiceInterface $scheduledPreviewService,
        private readonly LocaleConfigInterface $localeConfig,
    ) {
    }

    public function getTree(string $locale): ContentTree
    {
        $isDrafts = $this->draftPreviewService->isEnabled();
        $isScheduled = $this->scheduledPreviewService->isEnabled();

        $treeKey = $locale.($isDrafts ? ':drafts' : '').($isScheduled ? ':scheduled' : '');

        if (isset($this->trees[$treeKey])) {
            return $this->trees[$treeKey];
        }

        if (!$isDrafts && !$isScheduled) {
            $this->trees[$treeKey] = $this->cache->get(
                $this->cacheKey($locale),
                function (ItemInterface $item) use ($locale): ContentTree {
                    $item->expiresAfter(null);

                    return $this->contentTreeBuilder->build($locale);
                },
            );
        } else {
            $this->trees[$treeKey] = $this->contentTreeBuilder->build($locale, $isDrafts, $isScheduled);
        }

        return $this->trees[$treeKey];
    }

    public function findByUrl(string $url, string $locale): ?ContentItem
    {
        return $this->getTree($locale)->findByUrl($url);
    }

    public function findByDirectoryKey(string $directoryKey, string $locale): ?ContentItem
    {
        return $this->getTree($locale)->findByDirectoryKey($directoryKey);
    }

    /**
     * @return ContentItem[]
     */
    public function getPostsByCategory(string $category, string $locale): array
    {
        return $this->getTree($locale)->getPostsByCategory($category);
    }

    /**
     * @return ContentItem[]
     */
    public function getPostsByTag(string $tag, string $locale): array
    {
        return $this->getTree($locale)->getPostsByTag($tag);
    }

    /**
     * @return ContentItem[]
     */
    public function getPostsByYearMonth(int $year, int $month, string $locale): array
    {
        return $this->getTree($locale)->getPostsByYearMonth($year, $month);
    }

    /**
     * @return ContentItem[]
     */
    public function getPostsByYear(int $year, string $locale): array
    {
        return $this->getTree($locale)->getPostsByYear($year);
    }

    public function findPostBySlug(string $slug, string $locale): ?ContentItem
    {
        return $this->getTree($locale)->findPostBySlug($slug);
    }

    public function findScheduledPostBySlug(string $slug, string $locale): ?ContentItem
    {
        return $this->getTree($locale)->findScheduledPostBySlug($slug);
    }

    /**
     * @return ContentItem[]
     */
    public function getPosts(string $locale, int $page = 1, int $perPage = SiteConfigServiceInterface::DEFAULT_POSTS_PER_PAGE): array
    {
        $all = $this->getTree($locale)->getAllPosts();

        return array_slice($all, ($page - 1) * $perPage, $perPage);
    }

    public function getTotalPosts(string $locale): int
    {
        return count($this->getTree($locale)->getAllPosts());
    }

    /**
     * @return ContentItem[]
     */
    public function getRecentPosts(string $locale, int $limit = SiteConfigServiceInterface::DEFAULT_RECENT_POSTS_LIMIT): array
    {
        return array_slice($this->getTree($locale)->getAllPosts(), 0, $limit);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getTranslationMap(): array
    {
        if (null === $this->translationMap) {
            $trees = [];
            foreach ($this->localeConfig->getLocales() as $locale) {
                $trees[$locale] = $this->getTree($locale);
            }

            $this->translationMap = $this->translationMapBuilder->build($trees);
        }

        return $this->translationMap;
    }

    public function invalidateCache(string $locale): void
    {
        $this->cache->delete($this->cacheKey($locale));
        foreach (array_keys($this->trees) as $key) {
            if ($key === $locale || str_starts_with($key, $locale.':')) {
                unset($this->trees[$key]);
            }
        }

        $this->translationMap = null;
    }

    private function cacheKey(string $locale): string
    {
        return self::CACHE_KEY_PREFIX.$locale;
    }
}
