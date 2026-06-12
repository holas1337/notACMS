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
        $url = $this->generateUrl('projects_'.$locale);
        $page = $this->contentService->findByUrl($url, $locale);

        if (!$page instanceof ContentItem) {
            throw $this->createNotFoundException('Projects page not found');
        }

        $category = $page->slug();
        $allProjects = $this->contentService->getPostsByCategory($category, $locale);
        $featuredProjects = array_values(array_filter($allProjects, fn (ContentItem $contentItem): bool => $contentItem->isFeatured()));

        return $this->render('page/projects.html.twig', [
            'content' => $page,
            'featured_projects' => $featuredProjects,
            'total_projects' => count($allProjects),
            'locale' => $locale,
        ]);
    }
}
