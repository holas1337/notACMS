<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

/**
 * @implements \ArrayAccess<string, mixed>
 */
final readonly class SiteConfigData implements \ArrayAccess
{
    public string $name;

    public string $baseUrl;

    public string $description;

    public mixed $social;

    public mixed $author;

    /** @var array<string, mixed> */
    public array $locales;

    /**
     * @param array<string, mixed> $raw full _site.yaml "site" block, preserved as-is
     */
    private function __construct(
        private array $raw,
    ) {
        $this->name = (string) ($raw['name'] ?? '');
        $this->baseUrl = (string) ($raw['base_url'] ?? '');
        $this->description = (string) ($raw['description'] ?? '');
        $this->social = $raw['social'] ?? [];
        $this->author = $raw['author'] ?? [];
        $this->locales = (array) ($raw['locales'] ?? []);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->raw;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->raw[(string) $offset] ?? null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists((string) $offset, $this->raw);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('SiteConfigData is immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('SiteConfigData is immutable');
    }
}
