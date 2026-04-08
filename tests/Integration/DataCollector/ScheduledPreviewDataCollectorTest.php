<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\DataCollector;

use NotACms\DataCollector\ScheduledPreviewDataCollector;
use NotACms\Service\Preview\ScheduledPreviewServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ScheduledPreviewDataCollectorTest extends TestCase
{
    public function testCollectSetsEnabledState(): void
    {
        $service = $this->createStub(ScheduledPreviewServiceInterface::class);
        $service->method('isEnabled')->willReturn(true);

        $collector = new ScheduledPreviewDataCollector($service);
        $collector->collect(new Request(), new Response());

        self::assertTrue($collector->isEnabled());
    }

    public function testCollectSetsDisabledState(): void
    {
        $service = $this->createStub(ScheduledPreviewServiceInterface::class);
        $service->method('isEnabled')->willReturn(false);

        $collector = new ScheduledPreviewDataCollector($service);
        $collector->collect(new Request(), new Response());

        self::assertFalse($collector->isEnabled());
    }

    public function testGetName(): void
    {
        $service = $this->createStub(ScheduledPreviewServiceInterface::class);
        $collector = new ScheduledPreviewDataCollector($service);

        self::assertSame('app.scheduled_preview', $collector->getName());
    }

    public function testResetClearsData(): void
    {
        $service = $this->createStub(ScheduledPreviewServiceInterface::class);
        $service->method('isEnabled')->willReturn(true);

        $collector = new ScheduledPreviewDataCollector($service);
        $collector->collect(new Request(), new Response());
        $collector->reset();

        self::assertFalse($collector->isEnabled());
    }
}
