<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UrlsController;

$app->get('/', [HomeController::class, 'index'])->setName('home');

$app->get('/urls', [UrlsController::class, 'index'])->setName('urls');
$app->post('/urls', [UrlsController::class, 'store']);

$app->get('/urls/{id}', [UrlsController::class, 'show'])->setName('url_show');
$app->post('/urls/{id}', [UrlsController::class, 'check'])->setName('url_check');
