<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class ArchiveMonth
{
    public function __construct(
        public int $year,
        public int $month,
        public int $count,
    ) {
    }
}
