<?php

use DI\Container;
use Slim\Views\Twig;
use Slim\Factory\AppFactory;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';


$container = new Container();

AppFactory::setContainer($container);

$app = AppFactory::create();

$container->set('view', function () {
    $loader = new FilesystemLoader(__DIR__ . '/../resources/views');
    return new Twig($loader);
});

require __DIR__ . '/../routes/app.php';

$app->run();