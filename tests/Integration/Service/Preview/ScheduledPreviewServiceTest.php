<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Preview;

use NotACms\Service\Preview\ScheduledPreviewService;
use NotACms\Service\Preview\SessionToggleServiceInterface;
use PHPUnit\Framework\TestCase;

final class ScheduledPreviewServiceTest extends TestCase
{
    public function testIsEnabledDelegatesToSessionToggle(): void
    {
        $sessionToggle = $this->createStub(SessionToggleServiceInterface::class);
        $sessionToggle->method('isEnabled')->willReturn(true);

        $service = new ScheduledPreviewService($sessionToggle);

        self::assertTrue($service->isEnabled());
    }

    public function testIsDisabledByDefault(): void
    {
        $sessionToggle = $this->createStub(SessionToggleServiceInterface::class);
        $sessionToggle->method('isEnabled')->willReturn(false);

        $service = new ScheduledPreviewService($sessionToggle);

        self::assertFalse($service->isEnabled());
    }

    public function testToggleDelegatesToSessionToggle(): void
    {
        $sessionToggle = $this->createMock(SessionToggleServiceInterface::class);
        $sessionToggle->expects(self::once())->method('toggle');

        $service = new ScheduledPreviewService($sessionToggle);
        $service->toggle();
    }
}
