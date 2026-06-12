<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentTree;

interface ContentTreeProviderInterface
{
    public function getTree(string $locale): ContentTree;
}
