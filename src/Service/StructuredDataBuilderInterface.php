<?php

declare(strict_types=1);

namespace NotACms\Service;

use NotACms\Content\ValueObject\BlogPostingData;

interface StructuredDataBuilderInterface
{
    public const string SCHEMA_CONTEXT = 'https://schema.org';

    /**
     * @param array<string, mixed>|null $author
     * @param array<string, mixed>|null $searchAction
     *
     * @return array<string, mixed>
     */
    public function webSite(string $name, string $url, ?array $author = null, ?array $searchAction = null): array;

    /**
     * @param array<int, string> $expertiseTags
     * @param array<int, string> $socialUrls
     *
     * @return array<string, mixed>
     */
    public function person(string $name, ?string $url = null, ?string $jobTitle = null, ?string $description = null, ?string $email = null, array $expertiseTags = [], array $socialUrls = []): array;

    /**
     * @param BlogPostingData|array<string, mixed> $blogPostingData a BlogPostingData VO, or a named map (e.g. a Twig hash) matching its constructor
     *
     * @return array<string, mixed>
     */
    public function blogPosting(BlogPostingData|array $blogPostingData): array;

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<string, mixed>
     */
    public function collectionPage(string $name, string $url, array $items, ?string $description = null, ?int $numberOfItems = null): array;

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array;

    /**
     * @param array<string, mixed>|null $mainEntity
     *
     * @return array<string, mixed>
     */
    public function contactPage(string $name, string $url, ?array $mainEntity = null): array;

    /** @return array<string, mixed> */
    public function webPage(string $name, string $url, ?string $description = null): array;

    /** @return array<string, mixed> */
    public function organization(string $name, string $url): array;

    /** @return array<string, mixed> */
    public function imageObject(string $url, int $width, int $height, ?string $caption = null): array;
}
