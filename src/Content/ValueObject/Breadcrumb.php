<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class Breadcrumb
{
    public function __construct(
        public string $label,
        public ?string $url,
    ) {
    }
}
