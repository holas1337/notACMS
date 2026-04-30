<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Twig;

use NotACms\Content\ValueObject\SidebarData;
use NotACms\Service\Content\SidebarDataProviderInterface;
use NotACms\Twig\SidebarExtension;
use PHPUnit\Framework\TestCase;

final class SidebarExtensionTest extends TestCase
{
    private SidebarDataProviderInterface $provider;

    private SidebarExtension $extension;

    protected function setUp(): void
    {
        $this->provider = $this->createStub(SidebarDataProviderInterface::class);
        $this->extension = new SidebarExtension($this->provider);
    }

    public function testDelegatesToProviderAndReturnsSidebarData(): void
    {
        $expected = new SidebarData([], [], [], []);
        $this->provider->method('getData')->willReturn($expected);

        $result = $this->extension->getSidebarData('en');

        self::assertSame($expected, $result);
    }

    public function testReturnsNullOnProviderException(): void
    {
        $this->provider->method('getData')
            ->willThrowException(new \RuntimeException('tree not found'));

        $result = $this->extension->getSidebarData('en');

        self::assertNull($result);
    }

    public function testAcceptsAnyLocale(): void
    {
        $expected = new SidebarData([], [], [], []);
        $this->provider->method('getData')->willReturn($expected);

        $result = $this->extension->getSidebarData('pl');

        self::assertSame($expected, $result);
    }
}
