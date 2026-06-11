<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Content\ContentItem;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    #[Route('/llms.txt', name: 'llms_txt')]
    public function llmsTxt(): Response
    {
        $llmsLimit = $this->siteConfigService->getLlmsLimit();
        $postsByLocale = [];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            $postsByLocale[$locale] = array_slice($this->contentService->getTree($locale)->getAllPosts(), 0, $llmsLimit);
        }

        $defaultLocale = $this->siteConfigService->getDefaultLocale();
        $contentTree = $this->contentService->getTree($defaultLocale);
        $homeUrl = $this->generateUrl('home_'.$defaultLocale);
        $pages = array_values(array_filter(
            $contentTree->getAllPages(),
            fn (ContentItem $contentItem): bool => !$contentItem->isDynamic() && '' !== $contentItem->url() && $homeUrl !== $contentItem->url(),
        ));

        $response = $this->render('feed/llms.txt.twig', [
            'posts_by_locale' => $postsByLocale,
            'pages' => $pages,
        ]);
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $response;
    }

    #[Route('/robots.txt', name: 'robots')]
    public function robots(): Response
    {
        $response = $this->render('feed/robots.txt.twig');
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $response;
    }

    #[Route('/sitemap.xml', name: 'sitemap')]
    public function sitemap(): Response
    {
        $itemsByLocale = [];
        foreach ($this->siteConfigService->getLocales() as $locale) {
            $tree = $this->contentService->getTree($locale);
            $itemsByLocale[$locale] = array_merge($tree->getAllPosts(), $tree->getAllPages());
        }

        $response = $this->render('feed/sitemap.xml.twig', [
            'items_by_locale' => $itemsByLocale,
        ]);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    #[LocalizedRoute('static_page', path: '/{slug}/', requirements: ['slug' => '[a-z0-9-]+'], priority: -50)]
    public function staticPage(string $locale, string $slug): Response
    {
        $prefix = $this->siteConfigService->getUrlPrefix($locale);
        $url = $prefix.$slug.'/';

        $page = $this->contentService->findByUrl($url, $locale);
        if (!$page instanceof ContentItem) {
            throw $this->createNotFoundException('Page not found: '.$url);
        }

        $template = $page->template();
        if (1 !== preg_match('/^[a-z]+\/[a-z-]+$/', $template) || str_contains($template, '..')) {
            throw $this->createNotFoundException('Invalid template: '.$template);
        }

        return $this->render($template.'.html.twig', [
            'content' => $page,
            'locale' => $locale,
        ]);
    }
}
