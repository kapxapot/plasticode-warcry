<?php

namespace App\Controllers\Admin;

use Plasticode\Controllers\Admin\ImageUploadController;
use Plasticode\IO\File;
use Plasticode\IO\Image;

use App\Models\GalleryPicture;

class GalleryController extends ImageUploadController
{
	/**
	 * Adds gallery pictures to gallery author.
	 */
	protected function addImage($context, Image $image, $fileName)
	{
	    $picture = GalleryPicture::create($context);
	    
	    $picture->comment = File::getName($fileName);
	    $picture->pictureType = $image->imgType;
	    $picture->thumbType = $image->imgType;

	    $picture->publish();
        $picture->stamp();
        $picture->save(); // !!!

	    $this->gallery->saveImage($picture, $image);
	}
}
