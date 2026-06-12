<?php

declare(strict_types=1);

namespace NotACms\Service\StaticBuild;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final readonly class StaticPageRenderer implements StaticPageRendererInterface
{
    public function __construct(
        private HttpKernelInterface $httpKernel,
    ) {
    }

    public function render(string $url, bool $allowErrorStatus = false): string
    {
        $request = Request::create($url, Request::METHOD_GET);
        $request->attributes->set('_static_build', true);

        $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST, false);

        if (!$allowErrorStatus && Response::HTTP_BAD_REQUEST <= $response->getStatusCode()) {
            throw new \RuntimeException(sprintf('HTTP %d for %s', $response->getStatusCode(), $url));
        }

        return (string) $response->getContent();
    }

    public function writePage(string $outputDir, string $url, string $html): void
    {
        $normalizedPath = '/' === $url ? '' : rtrim($url, '/');
        new Filesystem()->dumpFile($outputDir.$normalizedPath.'/index.html', $html);
    }

    public function writeFeed(string $outputDir, string $url, string $content): void
    {
        if (str_ends_with($url, '/')) {
            new Filesystem()->dumpFile($outputDir.rtrim($url, '/').'/index.xml', $content);

            return;
        }

        new Filesystem()->dumpFile($outputDir.$url, $content);
    }

    public function writeFile(string $outputDir, string $relativePath, string $content): void
    {
        new Filesystem()->dumpFile($outputDir.'/'.$relativePath, $content);
    }
}
