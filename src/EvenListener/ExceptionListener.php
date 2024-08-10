<?php

declare(strict_types=1);

namespace App\EvenListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsEventListener]
class ExceptionListener
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Si el path es la página raíz, renderizar la vista Twig
        if ($path === '/') {
            $content = $this->twig->render('index_api/index.html.twig');
            $event->setResponse(
                new Response(
                    $content,
                    Response::HTTP_OK,
                    ['Content-Type' => 'text/html']
                )
            );

            return;
        }

        $exception = $event->getThrowable();
        $response = new JsonResponse([
            'error' => $exception->getMessage(),
        ]);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}