<?php

declare(strict_types=1);

namespace NotACms\Service\Content;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use NotACms\Content\ValueObject\ParsedMarkdown;
use NotACms\Service\SiteConfigServiceInterface;

final class MarkdownParser implements MarkdownParserInterface
{
    private ?MarkdownConverter $markdownConverter = null;

    public function __construct(
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function parse(string $markdown): ParsedMarkdown
    {
        if ('' !== $markdown && !str_ends_with($markdown, "\n")) {
            $markdown .= "\n";
        }

        $renderedContent = $this->getConverter()->convert($markdown);

        $frontMatter = [];
        if ($renderedContent instanceof RenderedContentWithFrontMatter) {
            $frontMatter = $renderedContent->getFrontMatter() ?? [];
        }

        return new ParsedMarkdown(
            frontMatter: is_array($frontMatter) ? $frontMatter : [],
            html: (string) $renderedContent,
        );
    }

    private function getConverter(): MarkdownConverter
    {
        if (!$this->markdownConverter instanceof MarkdownConverter) {
            $host = (string) parse_url($this->siteConfigService->getBaseUrl(), PHP_URL_HOST);

            $environment = new Environment([
                'external_link' => [
                    'internal_hosts' => [$host, 'www.'.$host],
                    'open_in_new_window' => true,
                    'html_class' => '',
                    'nofollow' => 'external',
                    'noopener' => 'external',
                    'noreferrer' => 'external',
                ],
                'heading_permalink' => [
                    'html_class' => 'heading-anchor',
                    'id_prefix' => '',
                    'fragment_prefix' => '',
                    'symbol' => '#',
                    'insert' => 'after',
                ],
            ]);

            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new GithubFlavoredMarkdownExtension());
            $environment->addExtension(new FrontMatterExtension());
            $environment->addExtension(new HeadingPermalinkExtension());
            $environment->addExtension(new AttributesExtension());
            $environment->addExtension(new ExternalLinkExtension());

            $this->markdownConverter = new MarkdownConverter($environment);
        }

        return $this->markdownConverter;
    }
}
