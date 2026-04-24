<?php

declare(strict_types=1);

namespace NotACms\Content\Enum;

enum FilterType: string
{
    case Archive = 'archive';
    case Tag = 'tag';
    case Category = 'category';
}
