<?php

declare(strict_types=1);

namespace NotACms\Service;

final class StructuredDataBuilder implements StructuredDataBuilderInterface
{
    /**
     * @param array<string, mixed>|null $author
     * @param array<string, mixed>|null $searchAction
     *
     * @return array<string, mixed>
     */
    public function webSite(string $name, string $url, ?array $author = null, ?array $searchAction = null): array
    {
        $data = [
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $url,
            'author' => $author,
            'potentialAction' => $searchAction,
        ];

        return $this->filter($data);
    }

    /**
     * @param array<int, string> $expertiseTags
     * @param array<int, string> $socialUrls
     *
     * @return array<string, mixed>
     */
    public function person(string $name, ?string $url = null, ?string $jobTitle = null, ?string $description = null, ?string $email = null, array $expertiseTags = [], array $socialUrls = []): array
    {
        $data = [
            '@type' => 'Person',
            'name' => $name,
            'url' => $url,
            'jobTitle' => $jobTitle,
            'description' => $description,
            'email' => $email,
            'knowsAbout' => array_values($expertiseTags),
            'sameAs' => array_values($socialUrls),
        ];

        return $this->filter($data);
    }

    /**
     * @param array<int, string>        $keywords
     * @param array<string, mixed>|null $image
     * @param array<string, mixed>|null $author
     * @param array<string, mixed>|null $publisher
     *
     * @return array<string, mixed>
     */
    public function blogPosting(string $headline, string $url, string $inLanguage, string $datePublished, ?string $dateModified = null, ?string $description = null, int $wordCount = 0, ?string $articleSection = null, array $keywords = [], ?array $image = null, ?array $author = null, ?array $publisher = null, ?string $mainEntityOfPage = null): array
    {
        $data = [
            '@type' => 'BlogPosting',
            'headline' => $headline,
            'url' => $url,
            'inLanguage' => $inLanguage,
            'datePublished' => $datePublished,
            'dateModified' => $dateModified,
            'description' => $description,
            'wordCount' => $wordCount,
            'articleSection' => $articleSection,
            'keywords' => $keywords,
            'image' => $image,
            'author' => $author,
            'publisher' => $publisher,
            'mainEntityOfPage' => $mainEntityOfPage ?? $url,
        ];

        return $this->filter($data);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<string, mixed>
     */
    public function collectionPage(string $name, string $url, array $items, ?string $description = null, ?int $numberOfItems = null): array
    {
        $mainEntity = [
            '@type' => 'ItemList',
            'itemListElement' => $items,
        ];

        $mainEntity['numberOfItems'] = $numberOfItems ?? \count($items);

        $data = [
            '@type' => 'CollectionPage',
            'name' => $name,
            'url' => $url,
            'description' => $description,
            'mainEntity' => $mainEntity,
        ];

        return $this->filter($data);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<string, mixed>
     */
    public function breadcrumbList(array $items): array
    {
        $data = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];

        return $this->filter($data);
    }

    /**
     * @param array<string, mixed>|null $mainEntity
     *
     * @return array<string, mixed>
     */
    public function contactPage(string $name, string $url, ?array $mainEntity = null): array
    {
        $data = [
            '@type' => 'ContactPage',
            'name' => $name,
            'url' => $url,
            'mainEntity' => $mainEntity,
        ];

        return $this->filter($data);
    }

    /** @return array<string, mixed> */
    public function webPage(string $name, string $url, ?string $description = null): array
    {
        $data = [
            '@type' => 'WebPage',
            'name' => $name,
            'url' => $url,
            'description' => $description,
        ];

        return $this->filter($data);
    }

    /** @return array<string, mixed> */
    public function organization(string $name, string $url): array
    {
        return [
            '@type' => 'Organization',
            'name' => $name,
            'url' => $url,
        ];
    }

    /** @return array<string, mixed> */
    public function imageObject(string $url, int $width, int $height, ?string $caption = null): array
    {
        $data = [
            '@type' => 'ImageObject',
            'url' => $url,
            'width' => $width,
            'height' => $height,
            'caption' => $caption,
        ];

        return $this->filter($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function filter(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                $filtered = $this->filter($value);

                if ([] === $filtered && '@type' !== $key) {
                    continue;
                }

                $result[$key] = $filtered;

                continue;
            }

            if (null === $value) {
                continue;
            }

            if ('' === $value) {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
