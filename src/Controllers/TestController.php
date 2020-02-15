<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        return get_class($this->router);

        die('done');

        //return $response;
    }
}
