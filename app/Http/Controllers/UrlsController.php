<?php

namespace App\Http\Controllers;

use DI\Container;
use App\Repository\DataBase;
use Slim\Routing\RouteContext;
use App\Repository\UrlRepository;
use App\Services\CheckUrlService;
use App\Http\Validators\UrlValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UrlsController extends Controller
{
    protected UrlRepository $urlRepository;
    protected CheckUrlService $checkUrl;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->urlRepository = new UrlRepository(DataBase::get()->connect());
        $this->checkUrl = new CheckUrlService($container);
    }

    public function index(Request $request, Response $response, array $args)
    {
        $urls = $this->urlRepository->getAllUrls();

        $lastCheck = $this->urlRepository->getLatestChecks();

        $lastCheckByUrlId = [];
        foreach ($lastCheck as $check) {
            $lastCheckByUrlId[$check['url_id']] = $check;
        }

        foreach ($urls as &$url) {
            if (isset($lastCheckByUrlId[$url['id']])) {
                $url['status_code'] = $lastCheckByUrlId[$url['id']]['status_code'];
                $url['last_check_at'] = $lastCheckByUrlId[$url['id']]['last_check_at'];
            } else {
                $url['status_code'] = null;
                $url['last_check_at'] = null;
            }
        }

        return $this->view->render($response, 'urls/urls.twig', ['urls' => $urls]);
    }

    public function store(Request $request, Response $response, array $args)
    {
        $url = (array) $request->getParsedBody();
        $urlName = $url['url']['name'] ?? '';

        $errors = UrlValidator::validate($urlName);

        if (!empty($errors)) {
            return $this->view->render($response->withStatus(422), 'urls/home.twig', ['errors' => $errors]);
        }


        $id = $this->urlRepository->getIdByName($urlName);

        if ($id) {
            $this->flash->addMessage('success', 'Страница уже существует');
        } else {
            $this->flash->addMessage('success', 'Страница успешно добавлена');
            $id = $this->urlRepository->create($urlName, (string) date('Y-m-d H:i:s'));
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor('urls.show', ['id' => (string) $id]);
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
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

        $url = $this->urlRepository->getRowById($id);

        if (isset($url['name'])) {
            $check = $this->checkUrl->checkUrl($url['name'], $id);
        } else {
            $this->flash->addMessage('errors', 'Не верный id');
            return $this->view->render($response->withStatus(404), 'urls/home.twig');
        }

        if ($check['status_code'] == 500 || $check['status_code'] == 404) {
            $this->flash->addMessage('errors', 'Произошла ошибка при проверке, не удалось подключиться');
        } else {
            $this->urlRepository->createUrlCheck($check);
            $this->flash->addMessage('success', 'Страница успешно проверена');
        }

        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $redirectUrl = $routeParser->urlFor('urls.show', ['id' => $id]);
        return $response->withHeader('Location', $redirectUrl)->withStatus(302);
    }
}
