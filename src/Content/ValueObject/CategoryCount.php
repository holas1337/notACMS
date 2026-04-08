<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class CategoryCount
{
    public function __construct(
        public string $slug,
        public int $count,
    ) {
    }
}
