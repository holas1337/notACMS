<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Service\Content;

use NotACms\Content\ContentTree;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\Content\ContentTreeBuilder;
use NotACms\Service\Content\ContentTreeBuilderInterface;
use NotACms\Service\SiteConfigServiceInterface;
use NotACms\Tests\TmpDirTrait;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class ContentTreeBuilderTest extends TestCase
{
    use TmpDirTrait;
    private string $tmpContentDir;

    private ContentTreeBuilderInterface $builder;

    protected function setUp(): void
    {
        $this->tmpContentDir = sys_get_temp_dir() . '/notacms_ctb_' . uniqid();
        mkdir($this->tmpContentDir . '/blog/test-post', 0755, true);
        mkdir($this->tmpContentDir . '/pages/about', 0755, true);

        $siteConfig = $this->createStub(SiteConfigServiceInterface::class);
        $siteConfig->method('getDefaultLocale')->willReturn('en');

        $parser = $this->createStub(\NotACms\Service\Content\MarkdownParserInterface::class);
        $parser->method('parse')->willReturnCallback(function (string $content): \NotACms\Content\ValueObject\ParsedMarkdown {
            preg_match('/^---\n(.*?)\n---\n(.*)$/s', $content, $matches);
            $frontMatter = [];
            $html = '';
            if (2 < count($matches)) {
                preg_match_all('/^(\w+):\s*(.+)$/m', $matches[1], $fmMatches, PREG_SET_ORDER);
                foreach ($fmMatches as $fm) {
                    $frontMatter[$fm[1]] = $fm[2];
                }
                $html = '<p>' . trim($matches[2]) . '</p>';
            }

            return new \NotACms\Content\ValueObject\ParsedMarkdown($frontMatter, $html);
        });

        $this->builder = new ContentTreeBuilder($parser, $siteConfig, $this->tmpContentDir);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpContentDir);
    }

    public function testBuildReturnsEmptyTreeForEmptyDir(): void
    {
        $emptyDir = sys_get_temp_dir() . '/notacms_empty_' . uniqid();
        mkdir($emptyDir, 0755, true);

        $builder = new ContentTreeBuilder(
            $this->createStub(\NotACms\Service\Content\MarkdownParserInterface::class),
            $this->createStub(SiteConfigServiceInterface::class),
            $emptyDir,
        );

        $tree = $builder->build('en');

        self::assertSame([], $tree->getAllPosts());
        self::assertSame([], $tree->getAllPages());

        $this->removeDir($emptyDir);
    }

    public function testBuildAddsPostsFromBlogDirectory(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Test Post\ndate: 2024-01-15\nslug: 2024/01/15/test-post\n---\n\nContent here.",
        );

        $tree = $this->builder->build('en');

        self::assertCount(1, $tree->getAllPosts());
        self::assertSame([], $tree->getAllPages());
    }

    public function testBuildAddsPagesFromPagesDirectory(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/pages/about/en.md',
            "---\ntitle: About\nmenu:\n  weight: 10\n---\n\nAbout content.",
        );

        $tree = $this->builder->build('en');

        self::assertCount(1, $tree->getAllPages());
        self::assertSame([], $tree->getAllPosts());
    }

    public function testBuildExcludesDraftsByDefault(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Draft Post\ndate: 2024-01-15\nslug: 2024/01/15/draft\ndraft: true\n---\n\nDraft content.",
        );

        $tree = $this->builder->build('en');

        self::assertSame([], $tree->getAllPosts());
    }

    public function testBuildIncludesDraftsWhenRequested(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Draft Post\ndate: 2024-01-15\nslug: 2024/01/15/draft\ndraft: true\n---\n\nDraft content.",
        );

        $tree = $this->builder->build('en', includeDrafts: true);

        self::assertCount(1, $tree->getAllPosts());
    }

    public function testBuildExcludesScheduledPostsByDefault(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Future Post\ndate: 2099-01-01\nslug: 2099/01/01/future\n---\n\nFuture content.",
        );

        $tree = $this->builder->build('en');

        self::assertSame([], $tree->getAllPosts());
    }

    public function testBuildIncludesScheduledPostsWhenRequested(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Future Post\ndate: 2099-01-01\nslug: 2099/01/01/future\n---\n\nFuture content.",
        );

        $tree = $this->builder->build('en', includeScheduled: true);

        self::assertCount(1, $tree->getAllPosts());
    }

    public function testBuildComputesUrlForDefaultLocale(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Test Post\ndate: 2024-01-15\nslug: 2024/01/15/test-post\n---\n\nContent.",
        );

        $tree = $this->builder->build('en');
        $posts = $tree->getAllPosts();

        self::assertSame('/2024/01/15/test-post/', $posts[0]->url());
    }

    public function testBuildComputesUrlForNonDefaultLocale(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/pl.md',
            "---\ntitle: Test Post PL\ndate: 2024-01-15\nslug: 2024/01/15/test-post\n---\n\nTreść.",
        );

        $tree = $this->builder->build('pl');
        $posts = $tree->getAllPosts();

        self::assertSame('/pl/2024/01/15/test-post/', $posts[0]->url());
    }

    public function testBuildSetsDirectoryKey(): void
    {
        file_put_contents(
            $this->tmpContentDir . '/blog/test-post/en.md',
            "---\ntitle: Test Post\ndate: 2024-01-15\nslug: 2024/01/15/test-post\n---\n\nContent.",
        );

        $tree = $this->builder->build('en');
        $posts = $tree->getAllPosts();

        self::assertSame('test-post', $posts[0]->directoryKey());
    }

    public function testBuildReturnsEmptyTreeForNonExistentDir(): void
    {
        $builder = new ContentTreeBuilder(
            $this->createStub(\NotACms\Service\Content\MarkdownParserInterface::class),
            $this->createStub(SiteConfigServiceInterface::class),
            '/nonexistent/path',
        );

        $tree = $builder->build('en');

        self::assertSame([], $tree->getAllPosts());
    }
}
