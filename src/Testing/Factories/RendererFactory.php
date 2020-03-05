<?php

namespace App\Testing\Factories;

use App\Core\Interfaces\RendererInterface;
use App\Core\Renderer;
use Slim\Views\Twig;

class RendererFactory
{
    public static function make() : RendererInterface
    {
        $view = new Twig(
            [
                'vendor/kapxapot/plasticode/views/bootstrap3/',
                'views/'
            ],
            ['debug' => true]
        );

        return new Renderer($view);
    }
}
