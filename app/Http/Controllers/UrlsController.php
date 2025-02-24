<?php

namespace App\Http\Controllers;

use Slim\Routing\RouteContext;
use App\Services\CheckUrlServices;
use App\Http\Validators\UrlValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UrlsController extends Controller
{
    public function index(Request $request, Response $response, array $args)
    {
        $url = $this->urlRepository->getAllWithLatestChecks();

        return $this->view->render($response, 'urls.twig', ['urls' => $url]);
    }

    public function store(Request $request, Response $response, array $args)
    {
        $url = $request->getParsedBody();
        $urlName = $url['url']['name'] ?? '';

        $errors = UrlValidator::validate($urlName);

        if (!empty($errors)) {
            return $this->view->render($response->withStatus(422), 'home.twig', ['errors' => $errors]);
        }

        $data = $this->urlRepository->createOrGetId($urlName, date('Y-m-d H:i:s'));


        if ($data['status']) {
            $this->flash->addMessage('success', 'Страница уже существует');
        } else {
            $this->flash->addMessage('success', 'Страница успешно добавлена');
        }



        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor('url_show', ['id' => $data['id']]);
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }

    public function show(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $url = $this->urlRepository->getRowById($id);

        $urlCheck = $this->urlRepository->getCheckUrlByUrlId($id);

        return $this->view->render($response, 'check.twig', ['url' => $url, 'checks' => $urlCheck]);
    }

    public function check(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $checkUrl = new CheckUrlServices();

        $url = $this->urlRepository->getRowById($id);

        if (isset($url['name'])) {
            $check = $checkUrl->checkUrl($url['name'], $id);
            $this->urlRepository->createUrlCheck($check);
        } else {
            $this->flash->addMessage('errors', 'Не верный id');
            return $this->view->render($response->withStatus(422), 'home.twig');
        }

        $this->urlRepository->createUrlCheck($check);

        $this->flash->addMessage('success', 'Страница успешно проверена');

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor('url_show', ['id' => $id]);
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}
