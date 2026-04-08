<?php

declare(strict_types=1);

namespace NotACms\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class LocalizedRoute
{
    /**
     * @param array<string, string> $requirements
     * @param string[]              $methods
     */
    public function __construct(
        public string $name,
        public string $path,
        public array $requirements = [],
        public array $methods = [],
        public int $priority = 0,
    ) {
    }
}
