<?php

declare(strict_types=1);

namespace NotACms\Twig;

use NotACms\Service\StructuredDataBuilderInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class StructuredDataExtension
{
    public function __construct(
        private StructuredDataBuilderInterface $structuredDataBuilder,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    #[AsTwigFunction(name: 'json_ld', isSafe: ['html'])]
    public function schemaLd(array $data): string
    {
        if (!isset($data['@context'])) {
            $data = ['@context' => StructuredDataBuilderInterface::SCHEMA_CONTEXT] + $data;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return "<script type=\"application/ld+json\">\n".$json."\n</script>";
    }

    #[AsTwigFunction(name: 'structured_data')]
    public function getBuilder(): StructuredDataBuilderInterface
    {
        return $this->structuredDataBuilder;
    }
}
