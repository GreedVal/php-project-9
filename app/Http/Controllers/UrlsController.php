<?php

namespace App\Http\Controllers;

use App\Http\Validators\UrlValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UrlsController extends Controller
{

    public function index(Request $request, Response $response, $args)
    {
        $url = $this->urlRepository->getAllWithLatestChecks();

        return $this->view->render($response, 'urls.twig', ['urls' => $url]);
    }

    public function store(Request $request, Response $response, $args)
    {
        $url = $request->getParsedBody();
        $urlName = $url['url']['name'] ?? '';

        $errors = UrlValidator::validate($urlName);
        
        if (!empty($errors)) {
            return $this->view->render($response->withStatus(422), 'home.phtml', ['errors' => $errors]);
        }

        return $this->view->render($response, 'urls.twig');
    }

    public function show(Request $request, Response $response, $args)
    {

    }

    public function check(Request $request, Response $response, $args)
    {
        return $this->view->render($response, 'urls.twig');
    }
}