<?php

namespace App\Controllers;

use App\Config\Parsing\BBContainerConfig;
use App\ViewModels\BluepostViewModel;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeRenderer;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Util\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestController extends Controller
{
    public function index(ServerRequestInterface $request, ResponseInterface $response)
    {

        die('done');

        //return $response;
    }
}
