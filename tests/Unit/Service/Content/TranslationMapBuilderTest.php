<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service\Content;

use NotACms\Content\ContentTree;
use NotACms\Service\Content\TranslationMapBuilder;
use NotACms\Tests\Unit\Fixtures\ContentItemFactory;
use PHPUnit\Framework\TestCase;

final class TranslationMapBuilderTest extends TestCase
{
    private TranslationMapBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new TranslationMapBuilder();
    }

    public function testBuildReturnsEmptyArrayForNoTrees(): void
    {
        $result = $this->builder->build([]);

        self::assertSame([], $result);
    }

    public function testBuildReturnsEmptyArrayForEmptyTree(): void
    {
        $tree = new ContentTree();
        $trees = ['en' => $tree];

        $result = $this->builder->build($trees);

        self::assertSame([], $result);
    }

    public function testBuildMapsDirectoryKeyToLocaleUrl(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], 'my-post', '/en/my-post');
        $tree->addPost($post);

        $result = $this->builder->build(['en' => $tree]);

        self::assertSame(['my-post' => ['en' => '/en/my-post']], $result);
    }

    public function testBuildIncludesMultipleLocales(): void
    {
        $enTree = new ContentTree();
        $enPost = ContentItemFactory::publishedPost([], 'my-post', '/en/my-post');
        $enTree->addPost($enPost);

        $plTree = new ContentTree();
        $plPost = ContentItemFactory::polish([], 'my-post', '/pl/moj-post');
        $plTree->addPost($plPost);

        $result = $this->builder->build(['en' => $enTree, 'pl' => $plTree]);

        self::assertSame([
            'my-post' => [
                'en' => '/en/my-post',
                'pl' => '/pl/moj-post',
            ],
        ], $result);
    }

    public function testBuildIgnoresItemsWithoutDirectoryKey(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::create([], '', 'en', null, null, '/my-post');
        $tree->addPost($post);

        $result = $this->builder->build(['en' => $tree]);

        self::assertSame([], $result);
    }

    public function testBuildIgnoresItemsWithEmptyUrl(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], 'my-post', '');
        $tree->addPost($post);

        $result = $this->builder->build(['en' => $tree]);

        self::assertSame([], $result);
    }

    public function testBuildIncludesItemsWithZeroUrl(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], 'my-post', '0');
        $tree->addPost($post);

        $result = $this->builder->build(['en' => $tree]);

        // URL '0' is not empty, so it's included
        self::assertSame(['my-post' => ['en' => '0']], $result);
    }

    public function testBuildIncludesPagesAndPosts(): void
    {
        $tree = new ContentTree();
        $post = ContentItemFactory::publishedPost([], 'my-post', '/my-post');
        $page = ContentItemFactory::page([], 'about', '/about');

        $tree->addPost($post);
        $tree->addPage($page);

        $result = $this->builder->build(['en' => $tree]);

        self::assertSame([
            'my-post' => ['en' => '/my-post'],
            'about' => ['en' => '/about'],
        ], $result);
    }

    public function testBuildHandlesMultipleItemsPerLocale(): void
    {
        $tree = new ContentTree();
        $post1 = ContentItemFactory::publishedPost([], 'post-1', '/post-1');
        $post2 = ContentItemFactory::publishedPost([], 'post-2', '/post-2');

        $tree->addPost($post1);
        $tree->addPost($post2);

        $result = $this->builder->build(['en' => $tree]);

        self::assertSame([
            'post-1' => ['en' => '/post-1'],
            'post-2' => ['en' => '/post-2'],
        ], $result);
    }

    public function testBuildHandlesPartialTranslations(): void
    {
        $enTree = new ContentTree();
        $enPost1 = ContentItemFactory::publishedPost([], 'post-1', '/post-1');
        $enPost2 = ContentItemFactory::publishedPost([], 'post-2', '/post-2');
        $enTree->addPost($enPost1);
        $enTree->addPost($enPost2);

        $plTree = new ContentTree();
        $plPost1 = ContentItemFactory::polish([], 'post-1', '/pl/post-1');
        $plTree->addPost($plPost1);

        $result = $this->builder->build(['en' => $enTree, 'pl' => $plTree]);

        self::assertSame([
            'post-1' => ['en' => '/post-1', 'pl' => '/pl/post-1'],
            'post-2' => ['en' => '/post-2'],
        ], $result);
    }
}
