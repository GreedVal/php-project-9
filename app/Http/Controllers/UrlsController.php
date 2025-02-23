<?php

namespace App\Http\Controllers;

use Slim\Routing\RouteContext;
use App\Services\CheckUrlServices;
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
            return $this->view->render($response, 'home.twig', ['errors' => $errors]);
        }

        $this->flash->addMessage('success', 'Сайт успешно добавлен!');

        $id = $this->urlRepository->createOrGetId($urlName, date('Y-m-d H:i:s'));

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor('url_show', ['id' => $id]);
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];

        $url = $this->urlRepository->getRowById($id);

        $urlCheck = $this->urlRepository->getCheckUrlByUrlId($id);

        return $this->view->render($response, 'check.twig', ['url' => $url, 'checks' => $urlCheck]);
    }

    public function check(Request $request, Response $response, $args)
    {   
        $id = $args['id'];
        
        $checkUrl = new CheckUrlServices();

        $url = $this->urlRepository->getRowById($id);

        $check = $checkUrl->checkUrl($url['name'], $id);

        $this->urlRepository->createUrlCheck($check);
        
        $this->flash->addMessage('success', 'Страница успешно проверена');

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor('url_show', ['id' => $id]);
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}