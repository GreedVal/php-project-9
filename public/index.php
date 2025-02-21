<?php

use DI\Container;
use Slim\Views\Twig;
use Slim\Flash\Messages;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;
use App\Http\Middleware\ErrorHandlerMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

AppFactory::setContainer($container);

$app = AppFactory::create();

$container->set('view', function () {
    $loader = new FilesystemLoader(__DIR__ . '/../resources/views');
    return new Twig($loader);
});

$container->set('flash', function () {
    return new Messages();
});

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorHandler = new ErrorHandlerMiddleware($app, $container->get('view'));
$errorMiddleware->setDefaultErrorHandler($errorHandler);

require __DIR__ . '/../routes/app.php';

$app->run();