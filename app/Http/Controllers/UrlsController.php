<?php

namespace App\Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UrlsController extends Controller
{
    public function index(Request $request, Response $response, $args)
    {
        return $this->view->render($response, 'urls.twig');
    }
}