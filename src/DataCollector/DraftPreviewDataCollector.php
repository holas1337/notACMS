<?php

declare(strict_types=1);

namespace NotACms\DataCollector;

use NotACms\Service\Preview\DraftPreviewServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

#[AutoconfigureTag('data_collector', ['template' => 'data_collector/draft_preview.html.twig', 'id' => 'app.draft_preview'])]
final class DraftPreviewDataCollector extends DataCollector
{
    public function __construct(private readonly DraftPreviewServiceInterface $draftPreviewService)
    {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [
            'enabled' => $this->draftPreviewService->isEnabled(),
        ];
    }

    public function isEnabled(): bool
    {
        return $this->data['enabled'] ?? false;
    }

    public function getName(): string
    {
        return 'app.draft_preview';
    }

    #[\Override]
    public function reset(): void
    {
        $this->data = [];
    }
}
