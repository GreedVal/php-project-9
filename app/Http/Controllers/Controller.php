<?php

namespace App\Http\Controllers;

use Slim\Views\Twig;
use DI\Container;

class Controller
{
    protected $view;

    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
    }
}