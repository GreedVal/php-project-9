<?php

namespace App\Http\Controllers;

use DI\Container;
use Slim\Flash\Messages;
use App\Repository\Connection;
use App\Repository\UrlRepository;
use Slim\Views\Twig;

class Controller
{
    protected Twig $view;
    protected Messages $flash;
    protected UrlRepository $urlRepository;

    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
        $this->flash = $container->get('flash');
        $this->urlRepository = new UrlRepository(Connection::get()->connect());
    }
}
