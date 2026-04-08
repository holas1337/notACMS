<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ValueObject\SidebarData;

interface SidebarDataProviderInterface
{
    public function getData(string $locale): SidebarData;
}
