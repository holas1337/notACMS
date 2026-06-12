<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

interface TagLangSwitchResolverInterface
{
    /**
     * @return array<string, string> other locale => tag page URL (or blog list when the tag has no posts there)
     */
    public function resolve(string $tag, string $locale): array;
}
