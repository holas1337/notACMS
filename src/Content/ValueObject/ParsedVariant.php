<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class ParsedVariant
{
    public function __construct(
        public string $originalFilename,
        public ?int $variantWidth,
    ) {
    }
}
