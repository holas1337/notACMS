<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Preview;

use NotACms\Service\Preview\SessionToggleService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class SessionToggleServiceTest extends TestCase
{
    public function testIsEnabledReturnsFalseWithNoRequest(): void
    {
        $requestStack = new RequestStack();
        $service = new SessionToggleService($requestStack, 'test_key');

        self::assertFalse($service->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenNotSet(): void
    {
        $requestStack = $this->createRequestStackWithSession();
        $service = new SessionToggleService($requestStack, 'test_key');

        self::assertFalse($service->isEnabled());
    }

    public function testIsEnabledReturnsTrueWhenSet(): void
    {
        $requestStack = $this->createRequestStackWithSession();
        $requestStack->getCurrentRequest()->getSession()->set('test_key', true);

        $service = new SessionToggleService($requestStack, 'test_key');

        self::assertTrue($service->isEnabled());
    }

    public function testToggleSetsTrueWhenFalse(): void
    {
        $requestStack = $this->createRequestStackWithSession();
        $service = new SessionToggleService($requestStack, 'test_key');

        $service->toggle();

        self::assertTrue($requestStack->getCurrentRequest()->getSession()->get('test_key'));
    }

    public function testToggleSetsFalseWhenTrue(): void
    {
        $requestStack = $this->createRequestStackWithSession();
        $requestStack->getCurrentRequest()->getSession()->set('test_key', true);

        $service = new SessionToggleService($requestStack, 'test_key');
        $service->toggle();

        self::assertFalse($requestStack->getCurrentRequest()->getSession()->get('test_key'));
    }

    public function testToggleDoesNothingWithoutSession(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $requestStack->push($request);

        $service = new SessionToggleService($requestStack, 'test_key');
        $service->toggle();

        self::assertFalse($service->isEnabled());
    }

    private function createRequestStackWithSession(): RequestStack
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack->push($request);

        return $requestStack;
    }
}
