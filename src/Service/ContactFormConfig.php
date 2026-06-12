<?php

declare(strict_types=1);

namespace NotACms\Service;

final readonly class ContactFormConfig
{
    public function __construct(
        public string $email,
        public string $from,
        public string $fromName,
        public string $topic,
    ) {
    }

    public function isComplete(): bool
    {
        return '' !== $this->email && '' !== $this->from;
    }
}
