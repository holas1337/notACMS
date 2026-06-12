<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Content\Enum\CardLayout;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    #[LocalizedRoute('home', path: '/')]
    public function home(string $locale): Response
    {
        $prefix = $this->siteConfigService->getUrlPrefix($locale);
        $page = $this->contentService->findByUrl($prefix, $locale);

        return $this->render('page/home.html.twig', [
            'content' => $page,
            'recentPosts' => $this->contentService->getRecentPosts($locale, $this->siteConfigService->getRecentPostsLimit()),
            'locale' => $locale,
            'card_layouts' => CardLayout::cycle(),
        ]);
    }
}
