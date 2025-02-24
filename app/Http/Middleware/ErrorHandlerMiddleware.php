<?php

namespace App\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Slim\App;
use Slim\Views\Twig;
use Psr\Container\ContainerInterface;

class ErrorHandlerMiddleware
{
    /** @var App<ContainerInterface> */
    protected App $app;
    protected Twig $view;

    /**
     * @param App<ContainerInterface> $app
     * @param Twig $view
     */
    public function __construct(App $app, Twig $view)
    {
        $this->app = $app;
        $this->view = $view;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $response = $this->app->getResponseFactory()->createResponse();

        if ($exception->getCode() === 404) {
            return $this->view->render($response, "404.twig")
                ->withStatus(404);
        }

        return $this->view->render($response, "500.twig")
            ->withStatus(500);
    }
}
