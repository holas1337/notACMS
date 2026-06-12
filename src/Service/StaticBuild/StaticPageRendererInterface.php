<?php

declare(strict_types=1);

namespace NotACms\Service\StaticBuild;

interface StaticPageRendererInterface
{
    /**
     * Renders a URL via a kernel sub-request; throws \RuntimeException on HTTP >= 400 unless $allowErrorStatus.
     */
    public function render(string $url, bool $allowErrorStatus = false): string;

    public function writePage(string $outputDir, string $url, string $html): void;

    public function writeFeed(string $outputDir, string $url, string $content): void;

    public function writeFile(string $outputDir, string $relativePath, string $content): void;
}
