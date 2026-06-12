<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Service\Preview\DraftPreviewServiceInterface;
use NotACms\Service\Preview\ScheduledPreviewServiceInterface;
use NotACms\Service\Preview\SessionToggleServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PreviewToggleController extends AbstractController
{
    public function __construct(
        private readonly DraftPreviewServiceInterface $draftPreviewService,
        private readonly ScheduledPreviewServiceInterface $scheduledPreviewService,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug,
    ) {
    }

    #[Route('/dev/drafts/toggle', name: 'dev_drafts_toggle', methods: ['GET'])]
    public function toggleDrafts(Request $request): RedirectResponse
    {
        return $this->toggle($request, $this->draftPreviewService);
    }

    #[Route('/dev/scheduled/toggle', name: 'dev_scheduled_toggle', methods: ['GET'])]
    public function toggleScheduled(Request $request): RedirectResponse
    {
        return $this->toggle($request, $this->scheduledPreviewService);
    }

    private function toggle(Request $request, SessionToggleServiceInterface $sessionToggleService): RedirectResponse
    {
        if (!$this->debug) {
            throw $this->createNotFoundException();
        }

        $sessionToggleService->toggle();

        $referer = (string) $request->headers->get('referer', '');
        $refererParts = parse_url($referer);
        $refererHost = is_array($refererParts) ? ($refererParts['host'] ?? null) : null;
        $refererPath = is_array($refererParts) ? ($refererParts['path'] ?? '') : '';

        if (
            (null === $refererHost || $request->getHost() === $refererHost)
            && str_starts_with($refererPath, '/')
            && !str_starts_with($refererPath, '//')
        ) {
            return $this->redirect($refererPath);
        }

        return $this->redirect('/');
    }
}
