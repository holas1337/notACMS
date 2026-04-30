<?php

declare(strict_types=1);

namespace NotACms\Content\ValueObject;

/**
 * @implements \ArrayAccess<string, array<string, string>>
 */
final readonly class TranslationMapData implements \ArrayAccess
{
    /**
     * @param array<string, array<string, string>> $map directoryKey => locale => url
     */
    public function __construct(
        private array $map,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function offsetGet(mixed $offset): array
    {
        return $this->map[(string) $offset] ?? [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists((string) $offset, $this->map);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('TranslationMapData is immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('TranslationMapData is immutable');
    }

    public function url(string $directoryKey, string $locale): ?string
    {
        return $this->map[$directoryKey][$locale] ?? null;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function all(): array
    {
        return $this->map;
    }
}
