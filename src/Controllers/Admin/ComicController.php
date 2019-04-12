<?php

namespace App\Controllers\Admin;

use Plasticode\Controllers\Admin\ImageUploadController;

use App\Services\ComicService;

class ComicController extends ImageUploadController
{
    private $comicService;
    
    public function __construct($controller)
    {
        parent::__construct($controller);
        
        $this->comicService = new ComicService();
    }
    
	/**
	 * Adds pages to comic issue or comic standalone.
	 */
	protected function addImage($context, $image, $fileName)
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
