<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Content\ContentItem;
use NotACms\Content\ValueObject\LangSwitchContext;
use NotACms\Service\Content\LangSwitchUrlResolverInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class LangSwitcherExtension
{
    public function __construct(
        private LangSwitchUrlResolverInterface $langSwitchUrlResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string>        $otherLocales
     *
     * @return array<string, string>
     */
    #[AsTwigFunction(name: 'lang_switch_urls', needsContext: true)]
    public function langSwitchUrls(array $context, array $otherLocales): array
    {
        $switchContent = $context['content'] ?? $context['index_content'] ?? null;
        $directoryKey = $switchContent instanceof ContentItem ? $switchContent->directoryKey() : null;

        $langSwitchContext = $context['lang_switch'] ?? new LangSwitchContext();
        if (!$langSwitchContext instanceof LangSwitchContext) {
            $langSwitchContext = new LangSwitchContext();
        }

        return $this->langSwitchUrlResolver->resolve(
            $directoryKey,
            $langSwitchContext,
            $context['translation_map'] ?? [],
            $otherLocales,
        );
    }
}
