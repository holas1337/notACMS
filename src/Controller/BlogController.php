<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Content\ContentItem;
use NotACms\Content\Enum\CardLayout;
use NotACms\Content\Enum\FilterType;
use NotACms\Content\ValueObject\LangSwitchContext;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\ContentTreeProviderInterface;
use NotACms\Service\Content\RelatedPostsServiceInterface;
use NotACms\Service\Content\TagLangSwitchResolverInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class BlogController extends AbstractController
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
        private readonly ContentTreeProviderInterface $contentTreeProvider,
        private readonly RelatedPostsServiceInterface $relatedPostsService,
        private readonly SiteConfigServiceInterface $siteConfigService,
        private readonly TagLangSwitchResolverInterface $tagLangSwitchResolver,
    ) {
    }

    #[LocalizedRoute('blog_list', path: '/blog/')]
    #[LocalizedRoute('blog_list_paginated', path: '/blog/page/{page}/', requirements: ['page' => '\d+'])]
    public function list(string $locale, int $page = 1): Response
    {
        $total = $this->contentService->getTotalPosts($locale);
        $totalPages = (int) ceil($total / $this->siteConfigService->getPostsPerPage());
        $page = max(1, min($page, max(1, $totalPages)));

        $context = $this->buildContext($locale);
        $context['posts'] = $this->contentService->getPosts($locale, $page, $this->siteConfigService->getPostsPerPage());
        $context['current_page'] = $page;
        $context['total_pages'] = $totalPages;
        $context['total_posts'] = $total;
        $context['filter_type'] = null;
        $context['filter_value'] = null;
        $context['index_content'] = $this->getIndexContent($locale);
        $context['lang_switch'] = new LangSwitchContext(currentPage: $page);

        return $this->render('blog/list.html.twig', $context);
    }

    #[LocalizedRoute('blog_category', path: '/blog/{category}/', requirements: ['category' => '[a-z0-9-]+'])]
    public function category(string $locale, string $category): Response
    {
        $post = $this->contentService->findPostBySlug($category, $locale);
        if ($post instanceof ContentItem) {
            return $this->renderPost($locale, $post);
        }

        $scheduled = $this->contentService->findScheduledPostBySlug($category, $locale);
        if ($scheduled instanceof ContentItem) {
            return $this->renderComingSoon($locale, $scheduled);
        }

        return $this->renderCategory($locale, $category);
    }

    #[LocalizedRoute('blog_tag', path: '/tag/{tag}/', requirements: ['tag' => '[a-z0-9-]+'])]
    public function tag(string $locale, string $tag): Response
    {
        $posts = $this->contentService->getPostsByTag($tag, $locale);
        if ([] === $posts) {
            throw $this->createNotFoundException('Tag not found: '.$tag);
        }

        $context = $this->buildContext($locale);
        $context['posts'] = $posts;
        $context['filter_type'] = FilterType::Tag->value;
        $context['filter_value'] = $tag;
        $context['current_page'] = 1;
        $context['total_pages'] = 1;
        $context['index_content'] = null;
        $context['lang_switch'] = new LangSwitchContext(
            urlOverrides: $this->tagLangSwitchResolver->resolve($tag, $locale),
            filterType: FilterType::Tag->value,
        );

        return $this->render('blog/list.html.twig', $context);
    }

    #[LocalizedRoute('blog_archive', path: '/archive/{year}/{month}/', requirements: ['year' => '\d{4}', 'month' => '\d{2}'])]
    public function archive(string $locale, int $year, int $month): Response
    {
        $posts = $this->contentService->getPostsByYearMonth($year, $month, $locale);
        if ([] === $posts) {
            throw $this->createNotFoundException(sprintf('No posts in archive %04d/%02d', $year, $month));
        }

        $context = $this->buildContext($locale);
        $context['posts'] = $posts;
        $context['filter_type'] = FilterType::Archive->value;
        $context['archive_year'] = $year;
        $context['archive_month'] = $month;
        $context['current_page'] = 1;
        $context['total_pages'] = 1;
        $context['index_content'] = null;
        $context['lang_switch'] = new LangSwitchContext(filterType: FilterType::Archive->value, archiveYear: $year, archiveMonth: $month);

        return $this->render('blog/list.html.twig', $context);
    }

    #[LocalizedRoute('blog_archive_year', path: '/archive/{year}/', requirements: ['year' => '\d{4}'])]
    public function archiveYear(string $locale, int $year): Response
    {
        $posts = $this->contentService->getPostsByYear($year, $locale);
        if ([] === $posts) {
            throw $this->createNotFoundException(sprintf('No posts in archive %04d', $year));
        }

        $context = $this->buildContext($locale);
        $context['posts'] = $posts;
        $context['filter_type'] = FilterType::Archive->value;
        $context['archive_year'] = $year;
        $context['current_page'] = 1;
        $context['total_pages'] = 1;
        $context['index_content'] = null;
        $context['lang_switch'] = new LangSwitchContext(filterType: FilterType::Archive->value, archiveYear: $year);

        return $this->render('blog/list.html.twig', $context);
    }

    #[LocalizedRoute('rss', path: '/feed/')]
    public function feed(string $locale): Response
    {
        $posts = $this->contentService->getRecentPosts($locale, $this->siteConfigService->getRssLimit());

        $response = $this->render('feed/rss.xml.twig', [
            'posts' => $posts,
            'locale' => $locale,
        ]);
        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContext(string $locale): array
    {
        return [
            'locale' => $locale,
            'card_layouts' => CardLayout::cycle(),
        ];
    }

    private function renderCategory(string $locale, string $category): Response
    {
        $posts = $this->contentService->getPostsByCategory($category, $locale);
        if ([] === $posts) {
            throw $this->createNotFoundException('Category not found: '.$category);
        }

        $context = $this->buildContext($locale);
        $context['posts'] = $posts;
        $context['filter_type'] = FilterType::Category->value;
        $context['filter_value'] = $category;
        $context['current_page'] = 1;
        $context['total_pages'] = 1;
        $context['index_content'] = $this->getIndexContent($locale, $category);
        $context['lang_switch'] = new LangSwitchContext(filterType: FilterType::Category->value);

        return $this->render('blog/list.html.twig', $context);
    }

    private function renderPost(string $locale, ContentItem $contentItem): Response
    {
        $context = $this->buildContext($locale);
        $context['content'] = $contentItem;
        $contentTree = $this->contentTreeProvider->getTree($locale);
        $context['related_posts'] = $this->relatedPostsService->getRelatedPosts($contentTree, $contentItem, $this->siteConfigService->getRelatedPostsLimit());
        $adjacentPosts = $contentTree->getAdjacentPosts($contentItem);
        $context['prev_post'] = $adjacentPosts->prev;
        $context['next_post'] = $adjacentPosts->next;

        $seriesPosts = null !== $contentItem->series() ? $contentTree->getSeriesPosts($contentItem->series()) : [];
        $seriesCurrentPart = $contentTree->getSeriesPosition($contentItem);

        $context['series_posts'] = $seriesPosts;
        $context['series_current_part'] = $seriesCurrentPart;

        return $this->render('blog/post.html.twig', $context);
    }

    private function renderComingSoon(string $locale, ContentItem $contentItem): Response
    {
        $context = $this->buildContext($locale);
        $context['post'] = $contentItem;

        return $this->render('page/coming-soon.html.twig', $context);
    }

    private function getIndexContent(string $locale, ?string $categorySlug = null): ?ContentItem
    {
        $listUrl = $this->generateUrl('blog_list_'.$locale);

        if (null === $categorySlug) {
            return $this->contentService->findByUrl($listUrl, $locale);
        }

        return $this->contentService->findByUrl($listUrl.$categorySlug.'/', $locale);
    }
}
