<?php

namespace App\Controllers\Admin;

use App\Models\ComicPage;
use Plasticode\Controllers\Admin\ImageUploadController;
use Plasticode\Gallery\Gallery;
use Plasticode\IO\Image;
use Psr\Container\ContainerInterface;

abstract class ComicController extends ImageUploadController
{
    private Gallery $comics;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->comics = $container->comics;
    }

    /**
     * Adds page to comic.
     */
    protected function addImage(array $context, Image $image, string $fileName) : void
    {
        $page = $this->createPage($context, $image->imgType);

        // todo: get rid of getObj(), an interface must be used here
        $this->comics->saveImage($page->getObj(), $image);
    }

    abstract protected function createPage(array $context, string $imgType) : ComicPage;
}
