<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class SearchController extends AbstractController
{
    #[LocalizedRoute('search', path: '/search/')]
    public function search(string $locale): Response
    {
        return $this->render('search/index.html.twig', [
            'locale' => $locale,
        ]);
    }
}
