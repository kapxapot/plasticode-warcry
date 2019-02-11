<?php

namespace App\Controllers\Admin;

use Plasticode\Controllers\Admin\ImageUploadController;

use App\Data\Tables;

class ComicController extends ImageUploadController
{
	/**
	 * Adds pages to comic issue or comic standalone.
	 */
	protected function addImage($context, $image, $fileName)
	{
	    $item = $this->db->create(Tables::COMIC_PAGES, $context);
	    $item->number = $this->db->getMaxComicPageNumber($context) + 1;
	    $item->type = $image->imgType;
	    $item->published = 1;

        $this->db->dirty(Tables::COMIC_PAGES, $item); // !!!!!
        
	    $item->save(); // !!!

	    $this->comics->saveImage($item, $image);
	}
}
