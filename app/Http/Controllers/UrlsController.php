<?php

namespace App\Http\Controllers;

use DI\Container;
use App\Repository\DataBase;
use Slim\Routing\RouteContext;
use App\Repository\UrlRepository;
use App\Services\CheckUrlServices;
use App\Http\Validators\UrlValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UrlsController extends Controller
{
    protected UrlRepository $urlRepository;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->urlRepository = new UrlRepository(DataBase::get()->connect());
    }

    public function index(Request $request, Response $response, array $args)
    {
        $url = $this->urlRepository->getAllWithLatestChecks();

        return $this->view->render($response, 'urls/urls.twig', ['urls' => $url]);
    }

    public function store(Request $request, Response $response, array $args)
    {
        $url = (array) $request->getParsedBody();
        $urlName = $url['url']['name'] ?? '';

        $errors = UrlValidator::validate($urlName);

        if (!empty($errors)) {
            return $this->view->render($response->withStatus(422), 'urls/home.twig', ['errors' => $errors]);
        }


        $idUrl = $this->urlRepository->getIdByName($urlName);

        if ($idUrl) {
            $this->flash->addMessage('success', 'Страница уже существует');
        } else {
            $this->flash->addMessage('success', 'Страница успешно добавлена');
            $id = $this->urlRepository->create($urlName, (string) date('Y-m-d H:i:s'));
        }

        return $response->withHeader('Location', RouteContext::fromRequest($request)->getRouteParser()->urlFor('urls.show', ['id' => $id]))
        ->withStatus(302);
    }

    public function show(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $url = $this->urlRepository->getRowById($id);

        $urlCheck = $this->urlRepository->getCheckUrlByUrlId($id);

        return $this->view->render($response, 'urls/check.twig', ['url' => $url, 'checks' => $urlCheck]);
    }

    public function check(Request $request, Response $response, array $args)
    {
        $id = $args['id'];

        $checkUrl = new CheckUrlServices();

        $url = $this->urlRepository->getRowById($id);

        if (isset($url['name'])) {
            $check = $checkUrl->checkUrl($url['name'], $id);
        } else {
            $this->flash->addMessage('errors', 'Не верный id');
            return $this->view->render($response->withStatus(404), 'urls/home.twig');
        }

        if ($check['status_code'] == 500) {
            $this->flash->addMessage('errors', 'Произошла ошибка при проверке, не удалось подключиться');
        } else {
            $this->urlRepository->createUrlCheck($check);
            $this->flash->addMessage('success', 'Страница успешно проверена');
        }

        return $response->withHeader('Location', RouteContext::fromRequest($request)->getRouteParser()->urlFor('urls.show', ['id' => $id]))
        ->withStatus(302);
    }
}
