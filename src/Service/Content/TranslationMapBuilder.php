<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentTree;
use NotACms\Content\ValueObject\TranslationMapData;

final class TranslationMapBuilder implements TranslationMapBuilderInterface
{
    /**
     * @param array<string, ContentTree> $trees
     */
    public function build(array $trees): TranslationMapData
    {
        $map = [];

        foreach ($trees as $locale => $tree) {
            foreach ($tree->getAllItems() as $item) {
                $key = $item->directoryKey();
                if (null === $key) {
                    continue;
                }

                if ('' === $item->url()) {
                    continue;
                }

                $map[$key][$locale] = $item->url();
            }
        }

        return new TranslationMapData($map);
    }
}
