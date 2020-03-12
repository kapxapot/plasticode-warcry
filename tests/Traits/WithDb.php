<?php

namespace App\Tests\Traits;

use Plasticode\Auth\Auth;
use Plasticode\Data\Db;
use Plasticode\Gallery\Gallery;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Util\Cases;
use Psr\Container\ContainerInterface;
use Slim\Container;

trait WithDb
{
    protected function initModels() : void
    {
        $container = new Container(
            [
                'db' => function (ContainerInterface $c) {
                    return new Db($c);
                },

                'auth' => function (ContainerInterface $c) {
                    return $this->createStub(Auth::class);
                },

                'linker' => function (ContainerInterface $c) {
                    return $this->linker;
                },

                'cases' => function (ContainerInterface $c) {
                    return $this->createStub(Cases::class);
                },

                'parser' => function (ContainerInterface $c) {
                    return new CompositeParser();
                },

                'gallery' => function (ContainerInterface $c) {
                    return $this->createStub(Gallery::class);
                },

                'userRepository' => function (ContainerInterface $c) {
                    return $this->createStub(UserRepositoryInterface::class);
                },

                'roleRepository' => function (ContainerInterface $c) {
                    return $this->createStub(RoleRepositoryInterface::class);
                },

                'menuItemRepository' => function (ContainerInterface $c) {
                    return $this->createStub(MenuItemRepositoryInterface::class);
                },

                'tagRepository' => function (ContainerInterface $c) {
                    return $this->createStub(TagRepositoryInterface::class);
                }
            ]
        );
    }
}
