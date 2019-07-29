<?php

namespace App\Controllers\Admin;

use App\Services\ComicService;
use Plasticode\Controllers\Admin\ImageUploadController;
use Plasticode\IO\Image;
use Psr\Container\ContainerInterface;

class ComicController extends ImageUploadController
{
    /**
     * Comic service
     *
     * @var \App\Services\ComicService;
     */
    private $comicService;
    
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        
        $this->comicService = new ComicService();
    }
    
    /**
     * Adds pages to comic issue or comic standalone
     */
    protected function addImage(array $context, Image $image, string $fileName)
    {
        $comic = $this->comicService->getComicByContext($context);
        
        $page = $comic->createPage();
        
        $page->number = $comic->maxPageNumber() + 1;
        $page->picType = $image->imgType;

        $page->publish();
        $page->stamp();
        $page->save();

        $this->comics->saveImage($page, $image);
    }
}
