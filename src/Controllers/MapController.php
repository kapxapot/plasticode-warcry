<?php

namespace App\Controllers;

use App\Repositories\Interfaces\ArticleRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MapController extends Controller
{
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->articleRepository = $container->articleRepository;
    }

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
                    'items' => $this
                        ->articleRepository
                        ->getAllPublishedOrphans(),
                    'no_disqus' => 1,
                    'no_social' => 1,
                ],
            ]
        );

        return $this->render($response, 'main/map/index.twig', $params);
    }
}
