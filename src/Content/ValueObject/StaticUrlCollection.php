<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class StaticUrlCollection
{
    /**
     * @param list<string> $urls
     * @param list<string> $notes
     */
    public function __construct(
        public array $urls,
        public array $notes,
    ) {
    }
}
