<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\DataCollector;

use NotACms\DataCollector\DraftPreviewDataCollector;
use NotACms\Service\Preview\DraftPreviewServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DraftPreviewDataCollectorTest extends TestCase
{
    public function testCollectSetsEnabledState(): void
    {
        $service = $this->createStub(DraftPreviewServiceInterface::class);
        $service->method('isEnabled')->willReturn(true);

        $collector = new DraftPreviewDataCollector($service);
        $collector->collect(new Request(), new Response());

        self::assertTrue($collector->isEnabled());
    }

    public function testCollectSetsDisabledState(): void
    {
        $service = $this->createStub(DraftPreviewServiceInterface::class);
        $service->method('isEnabled')->willReturn(false);

        $collector = new DraftPreviewDataCollector($service);
        $collector->collect(new Request(), new Response());

        self::assertFalse($collector->isEnabled());
    }

    public function testGetName(): void
    {
        $service = $this->createStub(DraftPreviewServiceInterface::class);
        $collector = new DraftPreviewDataCollector($service);

        self::assertSame('app.draft_preview', $collector->getName());
    }

    public function testResetClearsData(): void
    {
        $service = $this->createStub(DraftPreviewServiceInterface::class);
        $service->method('isEnabled')->willReturn(true);

        $collector = new DraftPreviewDataCollector($service);
        $collector->collect(new Request(), new Response());
        $collector->reset();

        self::assertFalse($collector->isEnabled());
    }
}
