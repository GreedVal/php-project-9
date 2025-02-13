<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("Hello, Slim!");
    return $response;
});

$app->run();