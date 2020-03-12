<?php

namespace App\Testing\Factories;

use App\Core\Interfaces\RendererInterface;
use App\Core\Renderer;
use Plasticode\Twig\TwigView;
use Slim\Views\Twig;

class RendererFactory
{
    public static function make() : RendererInterface
    {
        $twig = new Twig(
            [
                'vendor/kapxapot/plasticode/views/bootstrap3/',
                'views/'
            ],
            ['debug' => true]
        );

        $view = new TwigView($twig);

        return new Renderer($view);
    }
}
