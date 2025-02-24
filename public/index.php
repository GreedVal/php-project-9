<?php

use DI\Container;
use Slim\Views\Twig;
use Slim\Flash\Messages;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UrlsController;
use Psr\Http\Message\ServerRequestInterface;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

AppFactory::setContainer($container);

$app = AppFactory::create();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$container->set('view', function () {
    $loader = new FilesystemLoader(__DIR__ . '/../resources/views');
    return new Twig($loader);
});

$container->set('flash', function () {
    return new Messages();
});

$container->get('view')->getEnvironment()->addGlobal('flash', $container->get('flash'));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setDefaultErrorHandler(function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($container) {
    $response = $container->get('responseFactory')->createResponse();

    if ($exception->getCode() === 404) {
        return $container->get('view')->render($response, "404.twig")
            ->withStatus(404);
    }

    return $container->get('view')->render($response, "500.twig")
        ->withStatus(500);
});

$app->get('/', [HomeController::class, 'index'])->setName('home');
$app->get('/urls', [UrlsController::class, 'index'])->setName('urls');
$app->post('/urls', [UrlsController::class, 'store']);
$app->get('/urls/{id}', [UrlsController::class, 'show'])->setName('url_show');
$app->post('/urls/{id}', [UrlsController::class, 'check'])->setName('url_check');

$app->run();
