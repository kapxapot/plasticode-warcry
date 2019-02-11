<?php

namespace App\Controllers\Tests;

use Plasticode\Controllers\Controller;

class SmokeTestController extends Controller
{
    public function __invoke($request, $response, $args)
    {
        $pages = $this->getSettings('smoke_tests');
        $testResults = $this->test($pages);
        
        return $this->view->render($response, 'tests/smoke.twig', [
            'title' => 'Smoke Test',
            'results' => $testResults,
        ]);
    }
    
    private function test(array $pages)
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
