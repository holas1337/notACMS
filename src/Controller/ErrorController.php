<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Service\LocaleConfigInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ErrorController extends AbstractController
{
    public function __construct(
        private readonly LocaleConfigInterface $localeConfig,
    ) {
    }

    #[LocalizedRoute('error_404', path: '/404/')]
    public function notFound(string $locale): Response
    {
        return $this->renderErrorPage($locale, Response::HTTP_NOT_FOUND);
    }

    #[LocalizedRoute('error_500', path: '/500/')]
    public function serverError(string $locale): Response
    {
        return $this->renderErrorPage($locale, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function __invoke(Request $request): Response
    {
        $exception = $request->attributes->get('exception');
        $statusCode = $exception instanceof HttpException
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $locale = $this->localeConfig->detectLocaleFromPath($request->getPathInfo());

        return $this->renderErrorPage($locale, $statusCode);
    }

    private function renderErrorPage(string $locale, int $statusCode): Response
    {
        return $this->render('page/error.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Error',
            'locale' => $locale,
            'content' => null,
        ], new Response('', $statusCode));
    }
}
