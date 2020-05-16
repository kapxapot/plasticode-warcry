<?php

namespace App\Controllers\Admin;

use Plasticode\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlaygroundController extends Controller
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {
        return $this->render(
            $response,
            'admin/playground/index.twig',
            ['title' => 'Playground']
        );
    }
}
