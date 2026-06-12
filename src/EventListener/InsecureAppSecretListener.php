<?php

declare(strict_types=1);

namespace NotACms\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 255)]
final class InsecureAppSecretListener
{
    private const string PLACEHOLDER_SECRET = 'changeme';

    private bool $checked = false;

    public function __construct(
        #[Autowire('%env(APP_SECRET)%')]
        private readonly string $appSecret,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(RequestEvent $requestEvent): void
    {
        if ($this->checked || $this->debug || !$requestEvent->isMainRequest()) {
            return;
        }

        $this->checked = true;

        if (self::PLACEHOLDER_SECRET === $this->appSecret) {
            $this->logger->critical('APP_SECRET is the committed placeholder "changeme" — generate a real secret (php -r "echo bin2hex(random_bytes(32));") and set it in .env.local');
        }
    }
}
