<?php

declare(strict_types=1);

namespace NotACms\DataCollector;

use NotACms\Service\Preview\ScheduledPreviewServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

#[AutoconfigureTag('data_collector', ['template' => 'data_collector/scheduled_preview.html.twig', 'id' => 'app.scheduled_preview'])]
final class ScheduledPreviewDataCollector extends DataCollector
{
    public function __construct(private readonly ScheduledPreviewServiceInterface $scheduledPreviewService)
    {
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [
            'enabled' => $this->scheduledPreviewService->isEnabled(),
        ];
    }

    public function isEnabled(): bool
    {
        return $this->data['enabled'] ?? false;
    }

    public function getName(): string
    {
        return 'app.scheduled_preview';
    }

    #[\Override]
    public function reset(): void
    {
        $this->data = [];
    }
}
