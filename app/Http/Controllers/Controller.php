<?php

namespace App\Http\Controllers;

use DI\Container;
use Slim\Flash\Messages;
use Slim\Views\Twig;

class Controller
{
    protected Twig $view;
    protected Messages $flash;

    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
        $this->flash = $container->get('flash');
    }
}
