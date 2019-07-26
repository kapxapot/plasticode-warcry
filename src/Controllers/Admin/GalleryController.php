<?php

namespace App\Controllers\Admin;

use App\Models\GalleryPicture;
use Plasticode\Controllers\Admin\ImageUploadController;
use Plasticode\IO\File;
use Plasticode\IO\Image;

class GalleryController extends ImageUploadController
{
    /**
     * Adds gallery pictures to gallery author
     */
    protected function addImage(array $context, Image $image, string $fileName)
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
