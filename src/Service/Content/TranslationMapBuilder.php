<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use NotACms\Content\ContentTree;

final class TranslationMapBuilder implements TranslationMapBuilderInterface
{
    /**
     * @param array<string, ContentTree> $trees
     *
     * @return array<string, array<string, string>>
     */
    public function build(array $trees): array
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

        return $map;
    }
}
