<?php

namespace App\Http\Controllers;

use DI\Container;
use App\Repository\Connection;
use App\Repository\UrlRepository;

class Controller
{
    protected $view;
    protected $flash;
    protected UrlRepository $urlRepository;
    
    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
        $this->flash = $container->get('flash');
        $this->urlRepository = new UrlRepository(Connection::get()->connect());
    }
}