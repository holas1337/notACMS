<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Content\ContentItem;
use NotACms\Content\Enum\CardLayout;
use NotACms\Content\Enum\FilterType;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\RelatedPostsServiceInterface;
use NotACms\Service\Content\TagTranslationServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class BlogController extends AbstractController
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
        private readonly RelatedPostsServiceInterface $relatedPostsService,
        private readonly SiteConfigServiceInterface $siteConfigService,
        private readonly TagTranslationServiceInterface $tagTranslationService,
    ) {
    }

    #[LocalizedRoute('blog_list', path: '/blog/')]
    #[LocalizedRoute('blog_list_paginated', path: '/blog/page/{page}/', requirements: ['page' => '\d+'])]
    public function list(string $locale, int $page = 1): Response
    {
        $total = $this->contentService->getTotalPosts($locale);
        $totalPages = (int) ceil($total / $this->siteConfigService->getPostsPerPage());
        $page = max(1, min($page, max(1, $totalPages)));

        $ctx = $this->buildContext($locale);
        $ctx['posts'] = $this->contentService->getPosts($locale, $page, $this->siteConfigService->getPostsPerPage());
        $ctx['current_page'] = $page;
        $ctx['total_pages'] = $totalPages;
        $ctx['total_posts'] = $total;
        $ctx['filter_type'] = null;
        $ctx['filter_value'] = null;
        $ctx['index_content'] = $this->getIndexContent($locale);

        return $this->render('blog/list.html.twig', $ctx);
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
        $posts = $this->contentService->getTree($locale)->getPostsByTag($tag);
        if ([] === $posts) {
            throw $this->createNotFoundException('Tag not found: '.$tag);
        }

        $locales = $this->siteConfigService->getLocales();
        $otherLocales = array_values(array_filter($locales, fn (string $l): bool => $l !== $locale));
        $otherLocale = $otherLocales[0] ?? $locale;

        $otherTag = $this->tagTranslationService->translate($tag, $locale, $otherLocale);
        $otherTagPosts = $this->contentService->getTree($otherLocale)->getPostsByTag($otherTag);
        $langSwitchUrl = [] === $otherTagPosts
            ? $this->generateUrl('blog_list_'.$otherLocale)
            : $this->generateUrl('blog_tag_'.$otherLocale, ['tag' => $otherTag]);

        $ctx = $this->buildContext($locale);
        $ctx['posts'] = $posts;
        $ctx['filter_type'] = FilterType::Tag->value;
        $ctx['filter_value'] = $tag;
        $ctx['current_page'] = 1;
        $ctx['total_pages'] = 1;
        $ctx['index_content'] = null;
        $ctx['lang_switch_url'] = $langSwitchUrl;

        return $this->render('blog/list.html.twig', $ctx);
    }

    #[LocalizedRoute('blog_archive', path: '/archive/{year}/{month}/', requirements: ['year' => '\d{4}', 'month' => '\d{2}'])]
    public function archive(string $locale, int $year, int $month): Response
    {
        $posts = $this->contentService->getTree($locale)->getPostsByYearMonth($year, $month);

        $ctx = $this->buildContext($locale);
        $ctx['posts'] = $posts;
        $ctx['filter_type'] = FilterType::Archive->value;
        $ctx['archive_year'] = $year;
        $ctx['archive_month'] = $month;
        $ctx['current_page'] = 1;
        $ctx['total_pages'] = 1;
        $ctx['index_content'] = null;

        return $this->render('blog/list.html.twig', $ctx);
    }

    #[LocalizedRoute('blog_archive_year', path: '/archive/{year}/', requirements: ['year' => '\d{4}'])]
    public function archiveYear(string $locale, int $year): Response
    {
        $posts = $this->contentService->getTree($locale)->getPostsByYear($year);

        $ctx = $this->buildContext($locale);
        $ctx['posts'] = $posts;
        $ctx['filter_type'] = FilterType::Archive->value;
        $ctx['archive_year'] = $year;
        $ctx['current_page'] = 1;
        $ctx['total_pages'] = 1;
        $ctx['index_content'] = null;

        return $this->render('blog/list.html.twig', $ctx);
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
        $posts = $this->contentService->getTree($locale)->getPostsByCategory($category);
        if ([] === $posts) {
            throw $this->createNotFoundException('Category not found: '.$category);
        }

        $ctx = $this->buildContext($locale);
        $ctx['posts'] = $posts;
        $ctx['filter_type'] = FilterType::Category->value;
        $ctx['filter_value'] = $category;
        $ctx['current_page'] = 1;
        $ctx['total_pages'] = 1;
        $ctx['index_content'] = $this->getIndexContent($locale, $category);

        return $this->render('blog/list.html.twig', $ctx);
    }

    private function renderPost(string $locale, ContentItem $contentItem): Response
    {
        $ctx = $this->buildContext($locale);
        $ctx['content'] = $contentItem;
        $contentTree = $this->contentService->getTree($locale);
        $ctx['related_posts'] = $this->relatedPostsService->getRelatedPosts($contentTree, $contentItem, $this->siteConfigService->getRelatedPostsLimit());
        $adjacentPosts = $contentTree->getAdjacentPosts($contentItem);
        $ctx['prev_post'] = $adjacentPosts->prev;
        $ctx['next_post'] = $adjacentPosts->next;

        $seriesPosts = [];
        $seriesCurrentPart = 1;
        if (null !== $contentItem->series()) {
            $seriesPosts = $contentTree->getSeriesPosts($contentItem->series());
            foreach ($seriesPosts as $i => $seriesPost) {
                if ($seriesPost->url() === $contentItem->url()) {
                    $seriesCurrentPart = $i + 1;

                    break;
                }
            }
        }

        $ctx['series_posts'] = $seriesPosts;
        $ctx['series_current_part'] = $seriesCurrentPart;

        return $this->render('blog/post.html.twig', $ctx);
    }

    private function renderComingSoon(string $locale, ContentItem $contentItem): Response
    {
        $ctx = $this->buildContext($locale);
        $ctx['post'] = $contentItem;

        return $this->render('page/coming-soon.html.twig', $ctx);
    }

    private function getIndexContent(string $locale, ?string $categorySlug = null): ?ContentItem
    {
        $listUrl = $this->generateUrl('blog_list_'.$locale);
        $url = null === $categorySlug ? $listUrl : $listUrl.$categorySlug.'/';

        return $this->contentService->getTree($locale)->findByUrl($url);
    }
}
