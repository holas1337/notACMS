<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

final readonly class ArchiveYearData
{
    public function __construct(
        public int $year,
        public int $count,
    ) {
    }
}
