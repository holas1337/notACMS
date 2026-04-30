<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentTree;
use NotACms\Content\ValueObject\TranslationMapData;

interface TranslationMapBuilderInterface
{
    /**
     * @param array<string, ContentTree> $trees
     */
    public function build(array $trees): TranslationMapData;
}
