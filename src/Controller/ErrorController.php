<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Service\Content\SidebarDataProviderInterface;
use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ErrorController extends AbstractController
{
    public function __construct(
        private readonly SidebarDataProviderInterface $sidebarDataProvider,
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    #[LocalizedRoute('error_404', path: '/404/')]
    public function notFound(string $locale): Response
    {
        return $this->renderErrorPage($locale, 404);
    }

    #[LocalizedRoute('error_500', path: '/500/')]
    public function serverError(string $locale): Response
    {
        return $this->renderErrorPage($locale, 500);
    }

    public function __invoke(Request $request): Response
    {
        $exception = $request->attributes->get('exception');
        $statusCode = $exception instanceof HttpException
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $locale = $this->siteConfigService->detectLocaleFromPath($request->getPathInfo());

        return $this->renderErrorPage($locale, $statusCode);
    }

    private function renderErrorPage(string $locale, int $statusCode): Response
    {
        try {
            $sidebar = $this->sidebarDataProvider->getData($locale);
        } catch (\Throwable) {
            $sidebar = null;
        }

        return $this->render('page/error.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Error',
            'locale' => $locale,
            'sidebar' => $sidebar,
            'content' => null,
        ], new Response('', $statusCode));
    }
}
