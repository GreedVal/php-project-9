<?php

use DI\Container;
use Slim\Views\Twig;
use Slim\Flash\Messages;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UrlsController;
use App\Http\Middleware\ErrorHandlerMiddleware;

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

$errorHandler = new ErrorHandlerMiddleware($app, $container->get('view'));
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->get('/', [HomeController::class, 'index'])->setName('home');
$app->get('/urls', [UrlsController::class, 'index'])->setName('urls');
$app->post('/urls', [UrlsController::class, 'store']);
$app->get('/urls/{id}', [UrlsController::class, 'show'])->setName('url_show');
$app->post('/urls/{id}', [UrlsController::class, 'check'])->setName('url_check');

$app->run();
