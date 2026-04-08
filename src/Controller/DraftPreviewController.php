<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Service\Preview\DraftPreviewServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DraftPreviewController extends AbstractController
{
    public function __construct(
        private readonly DraftPreviewServiceInterface $draftPreviewService,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug,
    ) {
    }

    #[Route('/dev/drafts/toggle', name: 'dev_drafts_toggle', methods: ['GET'])]
    public function toggle(Request $request): RedirectResponse
    {
        if (!$this->debug) {
            throw $this->createNotFoundException();
        }

        $this->draftPreviewService->toggle();

        $referer = $request->headers->get('referer', '/');
        if ($referer && str_starts_with($referer, '/') && !str_starts_with($referer, '//')) {
            return $this->redirect($referer);
        }

        return $this->redirect('/');
    }
}
