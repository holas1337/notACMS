<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Content\ContentItem;
use NotACms\Service\Content\ContentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ProjectsController extends AbstractController
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
    ) {
    }

    #[LocalizedRoute('projects', path: '/projects/')]
    public function projects(string $locale): Response
    {
        $contentTree = $this->contentService->getTree($locale);
        $url = $this->generateUrl('projects_'.$locale);
        $page = $contentTree->findByUrl($url);

        if (!$page instanceof ContentItem) {
            throw $this->createNotFoundException('Projects page not found');
        }

        $category = $page->slug();
        $allProjects = $contentTree->getPostsByCategory($category);
        $featuredProjects = array_values(array_filter($allProjects, fn (ContentItem $contentItem): bool => $contentItem->isFeatured()));

        return $this->render('page/projects.html.twig', [
            'content' => $page,
            'featured_projects' => $featuredProjects,
            'total_projects' => count($allProjects),
            'locale' => $locale,
        ]);
    }
}
