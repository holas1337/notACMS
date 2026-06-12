<?php

declare(strict_types=1);

namespace NotACms\Service\StaticBuild;

use NotACms\Content\ValueObject\StaticUrlCollection;

interface StaticUrlCollectorInterface
{
    public function collect(): StaticUrlCollection;
}
