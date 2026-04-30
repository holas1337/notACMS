<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use NotACms\Service\Content\ContentServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class BreadcrumbExtension
{
    public function __construct(
        private ContentServiceInterface $contentService,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param array<string, mixed> $options Supported keys:
     *                                      - home_label: ?string          Override label for the home crumb (default: menuLabel of "home" page)
     *                                      - filter_type: ?string        'category' | 'tag' | 'archive' — only relevant for blog list pages
     *                                      - filter_value: ?string       Category name or tag name for filtered list pages
     *                                      - archive_date: ?string       Pre-computed archive date label (already formatted as "May 2026" or "2026")
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    #[AsTwigFunction(name: 'breadcrumbs')]
    public function getBreadcrumbs(?ContentItem $contentItem, string $locale, array $options = []): array
    {
        $contentTree = $this->contentService->getTree($locale);
        $crumbs = [];

        $home = $contentTree->findByDirectoryKey('home');
        $homeLabel = $options['home_label'] ?? $this->getLabel($home?->menuLabel(), 'Home');
        $crumbs[] = [
            'label' => $homeLabel,
            'url' => $this->urlGenerator->generate('home_'.$locale),
        ];

        if (!($contentItem instanceof ContentItem) || 'blog' === $contentItem->directoryKey()) {
            return $this->blogListCrumbs($crumbs, $locale, $options, $contentItem);
        }

        if ($contentItem->date() instanceof \DateTimeImmutable) {
            return $this->blogPostCrumbs($crumbs, $contentItem, $locale);
        }

        $crumbs[] = ['label' => $contentItem->title(), 'url' => null];

        return $crumbs;
    }

    /**
     * @param array<int, array<string, mixed>> $crumbs
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    private function blogPostCrumbs(array $crumbs, ContentItem $contentItem, string $locale): array
    {
        $blog = $this->contentService->getTree($locale)->findByDirectoryKey('blog');
        $crumbs[] = [
            'label' => $this->getLabel($blog?->menuLabel(), 'Blog'),
            'url' => $this->urlGenerator->generate('blog_list_'.$locale),
        ];

        if ($contentItem->category()) {
            $crumbs[] = [
                'label' => $contentItem->category(),
                'url' => $this->urlGenerator->generate('blog_category_'.$locale, ['category' => $contentItem->category()]),
            ];
        }

        $crumbs[] = ['label' => $contentItem->title(), 'url' => null];

        return $crumbs;
    }

    /**
     * @param array<int, array<string, mixed>> $crumbs
     * @param array<string, mixed>             $options
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    private function blogListCrumbs(array $crumbs, string $locale, array $options, ?ContentItem $contentItem): array
    {
        $blog = $this->contentService->getTree($locale)->findByDirectoryKey('blog');
        $crumbs[] = [
            'label' => $contentItem instanceof ContentItem
                ? $this->getLabel($contentItem->menuLabel(), '')
                : $this->getLabel($blog?->menuLabel(), 'Blog'),
            'url' => $this->urlGenerator->generate('blog_list_'.$locale),
        ];

        $filterType = $options['filter_type'] ?? null;
        $filterValue = $options['filter_value'] ?? null;

        if ('category' === $filterType) {
            $crumbs[] = ['label' => $filterValue, 'url' => null];
        } elseif ('tag' === $filterType) {
            $crumbs[] = ['label' => '#'.$filterValue, 'url' => null];
        } elseif ('archive' === $filterType) {
            $archiveDate = $options['archive_date'] ?? '';
            $crumbs[] = ['label' => (string) $archiveDate, 'url' => null];
        }

        return $crumbs;
    }

    private function getLabel(?string $value, string $fallback): string
    {
        if (null === $value || '' === $value) {
            return $fallback;
        }

        return $value;
    }
}
