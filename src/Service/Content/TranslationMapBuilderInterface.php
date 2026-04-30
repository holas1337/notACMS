<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentTree;

interface TranslationMapBuilderInterface
{
    /**
     * @param array<string, ContentTree> $trees
     *
     * @return array<string, array<string, string>> directoryKey => locale => url
     */
    public function build(array $trees): array;
}
