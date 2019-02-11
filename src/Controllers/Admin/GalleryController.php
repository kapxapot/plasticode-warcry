<?php

namespace App\Controllers\Admin;

use Plasticode\Controllers\Admin\ImageUploadController;
use Plasticode\IO\File;

use App\Data\Tables;

class GalleryController extends ImageUploadController
{
	/**
	 * Adds gallery pictures to gallery author.
	 */
	protected function addImage($context, $image, $fileName)
	{
	    $item = $this->db->create(Tables::GALLERY_PICTURES, $context);
	    $item->comment = File::getName($fileName);
	    $item->picture_type = $image->imgType;
	    $item->thumb_type = $image->imgType;
	    //$item->published = 1;

        $this->db->dirty(Tables::GALLERY_PICTURES, $item); // !!!!!
        
	    $item->save(); // !!!

	    $this->gallery->saveImage($item, $image);
	}
}
