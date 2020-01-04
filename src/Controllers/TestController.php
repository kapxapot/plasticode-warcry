<?php

namespace App\Controllers;

use App\Models\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $events = Event::getOrderedByStart();

        foreach ($events as $event) {
            var_dump($event);
        }

        die('done');

        //return $response;
    }
}
