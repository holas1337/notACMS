<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use NotACms\Content\Enum\FilterType;
use NotACms\Content\ValueObject\Breadcrumb;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\ContentTreeBuilderInterface;
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
     * @return list<Breadcrumb>
     */
    #[AsTwigFunction(name: 'breadcrumbs')]
    public function getBreadcrumbs(?ContentItem $contentItem, string $locale, array $options = []): array
    {
        $crumbs = [];

        $home = $this->contentService->findByDirectoryKey(ContentTreeBuilderInterface::HOME_DIRECTORY_KEY, $locale);
        $homeLabel = $options['home_label'] ?? $this->getLabel($home?->menuLabel(), 'Home');
        $crumbs[] = new Breadcrumb($homeLabel, $this->urlGenerator->generate('home_'.$locale));

        if (!($contentItem instanceof ContentItem) || ContentTreeBuilderInterface::BLOG_DIRECTORY_KEY === $contentItem->directoryKey()) {
            return $this->blogListCrumbs($crumbs, $locale, $options, $contentItem);
        }

        if ($contentItem->isPost()) {
            return $this->blogPostCrumbs($crumbs, $contentItem, $locale);
        }

        $crumbs[] = new Breadcrumb($contentItem->title(), null);

        return $crumbs;
    }

    /**
     * @param list<Breadcrumb> $crumbs
     *
     * @return list<Breadcrumb>
     */
    private function blogPostCrumbs(array $crumbs, ContentItem $contentItem, string $locale): array
    {
        $blog = $this->contentService->findByDirectoryKey(ContentTreeBuilderInterface::BLOG_DIRECTORY_KEY, $locale);
        $crumbs[] = new Breadcrumb($this->getLabel($blog?->menuLabel(), 'Blog'), $this->urlGenerator->generate('blog_list_'.$locale));

        $category = $contentItem->category();
        if (null !== $category) {
            $crumbs[] = new Breadcrumb($category, $this->urlGenerator->generate('blog_category_'.$locale, ['category' => $category]));
        }

        $crumbs[] = new Breadcrumb($contentItem->title(), null);

        return $crumbs;
    }

    /**
     * @param list<Breadcrumb>     $crumbs
     * @param array<string, mixed> $options
     *
     * @return list<Breadcrumb>
     */
    private function blogListCrumbs(array $crumbs, string $locale, array $options, ?ContentItem $contentItem): array
    {
        $blog = $this->contentService->findByDirectoryKey(ContentTreeBuilderInterface::BLOG_DIRECTORY_KEY, $locale);
        $crumbs[] = new Breadcrumb(
            $contentItem instanceof ContentItem
                ? $this->getLabel($contentItem->menuLabel(), '')
                : $this->getLabel($blog?->menuLabel(), 'Blog'),
            $this->urlGenerator->generate('blog_list_'.$locale),
        );

        $filterType = $options['filter_type'] ?? null;
        $filterValue = $options['filter_value'] ?? null;

        if (FilterType::Category->value === $filterType) {
            $crumbs[] = new Breadcrumb((string) $filterValue, null);
        } elseif (FilterType::Tag->value === $filterType) {
            $crumbs[] = new Breadcrumb('#'.$filterValue, null);
        } elseif (FilterType::Archive->value === $filterType) {
            $crumbs[] = new Breadcrumb((string) ($options['archive_date'] ?? ''), null);
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
