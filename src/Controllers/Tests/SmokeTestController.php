<?php

namespace App\Controllers\Tests;

use Plasticode\Controllers\Controller;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SmokeTestController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->appContext);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $pages = $this->getSettings('smoke_tests');
        $testResults = $this->test($pages);
        
        return $this->render(
            $response,
            'tests/smoke.twig',
            [
                'title' => 'Smoke Test',
                'results' => $testResults,
            ]
        );
    }
    
    private function test(array $pages) : array
    {
        $results = [];
        
        foreach ($pages as $page) {
            $url = $this->router->pathFor($page['route'], $page['args'] ?? []);
            $absUrl = $this->linker->abs($url);
            
            $text = @file_get_contents($absUrl);
            
            $results[] = [
                'url' => $absUrl,
                'on' => $text !== false,
            ];
        }
        
        return $results;
    }
}
