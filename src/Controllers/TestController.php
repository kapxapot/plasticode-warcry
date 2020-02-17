<?php

namespace App\Controllers;

use App\ViewModels\BluepostViewModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {
        $model = new BluepostViewModel('blue post', null, null, []);

        dd($model->toArray());

        die('done');

        //return $response;
    }
}
