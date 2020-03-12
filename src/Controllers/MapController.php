<?php

namespace App\Controllers;

use App\Models\Article;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MapController extends Controller
{
    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $params = $this->buildParams(
            [
                'sidebar' => [ 'stream', 'gallery' ],
                'params' => [
                    'title' => $this->getSettings('map.title'),
                    'items' => Article::publishedOrphans(),
                    'no_disqus' => 1,
                    'no_social' => 1,
                ],
            ]
        );

        return $this->render($response, 'main/map/index.twig', $params);
    }
}
