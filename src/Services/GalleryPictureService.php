<?php

namespace App\Services;

use App\Models\GalleryPicture;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Gallery\Gallery;

class GalleryPictureService
{
    /** @var Gallery */
    private $gallery;

    private function updateDimensions(GalleryPicture $picture) : GalleryPicture
    {
        $img = $this->gallery->loadPicture($picture);
        
        if (is_null($img) || $img->width <= 0 || $img->height <= 0) {
            throw new InvalidResultException(
                'Invalid image file for gallery picture ' . $picture . '.'
            );
        }
        
        return $picture
            ->withWidth($img->width)
            ->withHeight($img->height);
    }
    
    private function updateAvgColor(GalleryPicture $picture) : GalleryPicture
    {
        $avgColor = $this->gallery->getAvgColor($picture);

        return $picture->withAvgColor($avgColor);
    }
}
