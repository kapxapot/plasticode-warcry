<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TagController extends Controller
{
    public function item(ServerRequestInterface $request, ResponseInterface $response, array $args) : ResponseInterface
    {
        $tag = $args['tag'];

        if (strlen($tag) == 0) {
            return $this->notFound($request, $response);
        }
        
        $parts = $this->tagPartsProviderService->getParts($tag);

        $params = $this->buildParams(
            [
                'sidebar' => [ 'stream', 'gallery' ],
                'params' => [
                    'tag' => $tag,
                    'title' => "Тег «{$tag}»", 
                    'parts' => $parts,
                ],
            ]
        );
    
        return $this->render($response, 'main/tags/item.twig', $params);
    }
}
