<?php

namespace App\Controllers;

use App\Services\TagPartsProviderService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TagController extends Controller
{
    /** @var TagPartsProviderService */
    private $tagPartsProviderService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->tagPartsProviderService = $container->tagPartsProviderService;
    }

    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $tag = $args['tag'];

        if (strlen($tag) == 0) {
            return $this->notFound($request, $response);
        }
        
        $parts = $this->tagPartsProviderService->getParts($tag);

        $params = $this->buildParams(
            [
                'sidebar' => ['stream', 'gallery'],
                'params' => [
                    'tag' => $tag,
                    'title' => 'Тег «' . $tag . '»', 
                    'parts' => $parts,
                ],
            ]
        );
    
        return $this->render($response, 'main/tags/item.twig', $params);
    }
}
