<?php

declare(strict_types=1);

namespace NotACms\Tests\Unit\Service;

use NotACms\Service\StructuredDataBuilder;
use NotACms\Service\StructuredDataBuilderInterface;
use PHPUnit\Framework\TestCase;

final class StructuredDataBuilderTest extends TestCase
{
    private StructuredDataBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new StructuredDataBuilder();
    }

    public function testWebSite_BuildsCorrectSchema(): void
    {
        $result = $this->builder->webSite('My Site', 'https://example.com');

        self::assertArrayNotHasKey('@context', $result);
        self::assertSame('WebSite', $result['@type']);
        self::assertSame('My Site', $result['name']);
        self::assertSame('https://example.com', $result['url']);
        self::assertArrayNotHasKey('author', $result);
        self::assertArrayNotHasKey('potentialAction', $result);
    }

    public function testWebSite_WithAuthorAndSearchAction(): void
    {
        $author = $this->builder->person('John', 'https://example.com');
        $result = $this->builder->webSite('My Site', 'https://example.com', $author, ['@type' => 'SearchAction']);

        self::assertSame('Person', $result['author']['@type']);
        self::assertSame('John', $result['author']['name']);
        self::assertArrayHasKey('potentialAction', $result);
    }

    public function testPerson_BuildsMinimalSchema(): void
    {
        $result = $this->builder->person('John');

        self::assertSame('Person', $result['@type']);
        self::assertSame('John', $result['name']);
        self::assertArrayNotHasKey('url', $result);
        self::assertArrayNotHasKey('jobTitle', $result);
    }

    public function testPerson_BuildsFullSchema(): void
    {
        $result = $this->builder->person(
            'John',
            'https://example.com',
            'Developer',
            'A person',
            'john@example.com',
            ['PHP', 'Symfony'],
            ['https://github.com/john', 'https://x.com/john']
        );

        self::assertSame('Person', $result['@type']);
        self::assertSame('John', $result['name']);
        self::assertSame('https://example.com', $result['url']);
        self::assertSame('Developer', $result['jobTitle']);
        self::assertSame('A person', $result['description']);
        self::assertSame('john@example.com', $result['email']);
        self::assertSame(['PHP', 'Symfony'], $result['knowsAbout']);
        self::assertSame(['https://github.com/john', 'https://x.com/john'], $result['sameAs']);
    }

    public function testPerson_StripsEmptyTagsAndUrls(): void
    {
        $result = $this->builder->person('John', null, null, null, null, [], []);

        self::assertArrayNotHasKey('knowsAbout', $result);
        self::assertArrayNotHasKey('sameAs', $result);
    }

    public function testBlogPosting_BuildsFullSchema(): void
    {
        $author = $this->builder->person('John', 'https://example.com');
        $publisher = $this->builder->organization('My Org', 'https://example.com');

        $result = $this->builder->blogPosting([
            'headline' => 'My Post',
            'url' => 'https://example.com/posts/my-post',
            'inLanguage' => 'en',
            'datePublished' => '2024-01-15T10:00:00+00:00',
            'dateModified' => '2024-01-16T12:00:00+00:00',
            'description' => 'A description',
            'wordCount' => 1500,
            'articleSection' => 'Technology',
            'keywords' => ['php', 'symfony'],
            'image' => $this->builder->imageObject('https://example.com/img.webp', 1280, 720),
            'author' => $author,
            'publisher' => $publisher,
            'mainEntityOfPage' => 'https://example.com/posts/my-post',
        ]);

        self::assertSame('BlogPosting', $result['@type']);
        self::assertSame('My Post', $result['headline']);
        self::assertSame('en', $result['inLanguage']);
        self::assertSame('2024-01-15T10:00:00+00:00', $result['datePublished']);
        self::assertSame('2024-01-16T12:00:00+00:00', $result['dateModified']);
        self::assertSame(1500, $result['wordCount']);
        self::assertSame('Technology', $result['articleSection']);
        self::assertSame(['php', 'symfony'], $result['keywords']);
        self::assertSame('ImageObject', $result['image']['@type']);
        self::assertSame(1280, $result['image']['width']);
        self::assertSame('Person', $result['author']['@type']);
        self::assertSame('Organization', $result['publisher']['@type']);
        self::assertSame('https://example.com/posts/my-post', $result['mainEntityOfPage']);
    }

    public function testBlogPosting_FiltersEmptyConditionalFields(): void
    {
        $result = $this->builder->blogPosting([
            'headline' => 'Title',
            'url' => 'https://example.com/title',
            'inLanguage' => 'en',
            'datePublished' => '2024-01-15T10:00:00+00:00',
        ]);

        self::assertArrayNotHasKey('dateModified', $result);
        self::assertArrayNotHasKey('description', $result);
        self::assertArrayNotHasKey('articleSection', $result);
        self::assertArrayNotHasKey('keywords', $result);
        self::assertArrayNotHasKey('image', $result);
        self::assertArrayNotHasKey('author', $result);
        self::assertArrayNotHasKey('publisher', $result);
    }

    public function testBlogPosting_AutoSetsMainEntityOfPage(): void
    {
        $result = $this->builder->blogPosting([
            'headline' => 'Title',
            'url' => 'https://example.com/title',
            'inLanguage' => 'en',
            'datePublished' => '2024-01-15T10:00:00+00:00',
        ]);

        self::assertSame('https://example.com/title', $result['mainEntityOfPage']);
    }

    public function testBlogPosting_ExplicitMainEntityOfPage(): void
    {
        $result = $this->builder->blogPosting([
            'headline' => 'Title',
            'url' => 'https://example.com/title',
            'inLanguage' => 'en',
            'datePublished' => '2024-01-15T10:00:00+00:00',
            'mainEntityOfPage' => 'https://example.com/custom',
        ]);

        self::assertSame('https://example.com/custom', $result['mainEntityOfPage']);
    }

    public function testCollectionPage_BuildsCorrectSchema(): void
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'url' => 'https://example.com/1'],
            ['@type' => 'ListItem', 'position' => 2, 'url' => 'https://example.com/2'],
            ['@type' => 'ListItem', 'position' => 3, 'url' => 'https://example.com/3'],
        ];

        $result = $this->builder->collectionPage('Blog', 'https://example.com/blog', $items, 'Blog description', null);

        self::assertSame('CollectionPage', $result['@type']);
        self::assertSame('Blog', $result['name']);
        self::assertSame('Blog description', $result['description']);
        self::assertSame('ItemList', $result['mainEntity']['@type']);
        self::assertCount(3, $result['mainEntity']['itemListElement']);
        self::assertSame(3, $result['mainEntity']['numberOfItems']);
    }

    public function testCollectionPage_ExplicitNumberOfItems(): void
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'url' => 'https://example.com/1'],
        ];

        $result = $this->builder->collectionPage('Blog', 'https://example.com/blog', $items, null, 42);

        self::assertSame(42, $result['mainEntity']['numberOfItems']);
    }

    public function testCollectionPage_WithoutDescription(): void
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'url' => 'https://example.com/1'],
        ];

        $result = $this->builder->collectionPage('Blog', 'https://example.com/blog', $items);

        self::assertArrayNotHasKey('description', $result);
    }

    public function testBreadcrumbList_BuildsCorrectSchema(): void
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://example.com/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => 'https://example.com/blog'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => 'Post'],
        ];

        $result = $this->builder->breadcrumbList($items);

        self::assertSame('BreadcrumbList', $result['@type']);
        self::assertCount(3, $result['itemListElement']);
        self::assertSame('Home', $result['itemListElement'][0]['name']);
        self::assertArrayHasKey('item', $result['itemListElement'][0]);
        self::assertArrayNotHasKey('item', $result['itemListElement'][2]);
    }

    public function testContactPage_BuildsCorrectSchema(): void
    {
        $mainEntity = $this->builder->person('John', 'https://example.com');
        $result = $this->builder->contactPage('Contact', 'https://example.com/contact', $mainEntity);

        self::assertSame('ContactPage', $result['@type']);
        self::assertSame('Contact', $result['name']);
        self::assertSame('Person', $result['mainEntity']['@type']);
    }

    public function testContactPage_WithoutMainEntity(): void
    {
        $result = $this->builder->contactPage('Contact', 'https://example.com/contact');

        self::assertArrayNotHasKey('mainEntity', $result);
    }

    public function testWebPage_BuildsCorrectSchema(): void
    {
        $result = $this->builder->webPage('My Page', 'https://example.com/page', 'A page description');

        self::assertSame('WebPage', $result['@type']);
        self::assertSame('My Page', $result['name']);
        self::assertSame('https://example.com/page', $result['url']);
        self::assertSame('A page description', $result['description']);
    }

    public function testWebPage_WithoutDescription(): void
    {
        $result = $this->builder->webPage('My Page', 'https://example.com/page');

        self::assertArrayNotHasKey('description', $result);
    }

    public function testOrganization_BuildsCorrectSchema(): void
    {
        $result = $this->builder->organization('My Org', 'https://example.com');

        self::assertSame('Organization', $result['@type']);
        self::assertSame('My Org', $result['name']);
        self::assertSame('https://example.com', $result['url']);
        self::assertArrayNotHasKey('@context', $result);
    }

    public function testImageObject_BuildsCorrectSchema(): void
    {
        $result = $this->builder->imageObject('https://example.com/img.webp', 1280, 720, 'A caption');

        self::assertSame('ImageObject', $result['@type']);
        self::assertSame('https://example.com/img.webp', $result['url']);
        self::assertSame(1280, $result['width']);
        self::assertSame(720, $result['height']);
        self::assertSame('A caption', $result['caption']);
        self::assertArrayNotHasKey('@context', $result);
    }

    public function testImageObject_WithoutCaption(): void
    {
        $result = $this->builder->imageObject('https://example.com/img.webp', 1280, 720);

        self::assertArrayNotHasKey('caption', $result);
    }

    public function testFilter_StripsNullEmptyAndEmptyArrays(): void
    {
        $data = [
            '@type' => 'Test',
            'keep' => 'value',
            'nullVal' => null,
            'emptyStr' => '',
            'zeroInt' => 0,
            'falseBool' => false,
            'emptyArr' => [],
            'nestedNull' => ['@type' => 'Nested', 'val' => null],
        ];

        $reflection = new \ReflectionClass($this->builder);
        $method = $reflection->getMethod('filter');

        $result = $method->invoke($this->builder, $data);

        self::assertArrayHasKey('keep', $result);
        self::assertSame('value', $result['keep']);
        self::assertArrayNotHasKey('nullVal', $result);
        self::assertArrayNotHasKey('emptyStr', $result);
        self::assertArrayHasKey('zeroInt', $result);
        self::assertSame(0, $result['zeroInt']);
        self::assertArrayHasKey('falseBool', $result);
        self::assertFalse($result['falseBool']);
        self::assertArrayNotHasKey('emptyArr', $result);
        self::assertArrayHasKey('nestedNull', $result);
        self::assertSame('Nested', $result['nestedNull']['@type']);
        self::assertArrayNotHasKey('val', $result['nestedNull']);
    }

    public function testFilter_PreservesTopLevelType(): void
    {
        $data = [
            '@context' => StructuredDataBuilderInterface::SCHEMA_CONTEXT,
            '@type' => 'Test',
            'data' => [],
            'other' => null,
        ];

        $reflection = new \ReflectionClass($this->builder);
        $method = $reflection->getMethod('filter');

        $result = $method->invoke($this->builder, $data);

        self::assertArrayHasKey('@context', $result);
        self::assertArrayHasKey('@type', $result);
        self::assertSame('Test', $result['@type']);
        self::assertArrayNotHasKey('data', $result);
        self::assertArrayNotHasKey('other', $result);
    }

    public function testSubStructureHelpersHaveNoContext(): void
    {
        $person = $this->builder->person('John');
        $org = $this->builder->organization('Org', 'https://example.com');
        $img = $this->builder->imageObject('https://example.com/img.webp', 100, 100);

        self::assertArrayNotHasKey('@context', $person);
        self::assertArrayNotHasKey('@context', $org);
        self::assertArrayNotHasKey('@context', $img);
    }
}
