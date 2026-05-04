<?php

declare(strict_types=1);

namespace NotACms\Service;

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
     * @param array<int, string>        $keywords
     * @param array<string, mixed>|null $image
     * @param array<string, mixed>|null $author
     * @param array<string, mixed>|null $publisher
     *
     * @return array<string, mixed>
     */
    public function blogPosting(string $headline, string $url, string $inLanguage, string $datePublished, ?string $dateModified = null, ?string $description = null, int $wordCount = 0, ?string $articleSection = null, array $keywords = [], ?array $image = null, ?array $author = null, ?array $publisher = null, ?string $mainEntityOfPage = null): array;

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
