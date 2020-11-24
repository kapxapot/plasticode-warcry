<?php

namespace App\Controllers\Admin;

use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Controllers\Admin\ImageUploadController;
use Plasticode\Gallery\Gallery;
use Plasticode\IO\File;
use Plasticode\IO\Image;
use Psr\Container\ContainerInterface;

class GalleryController extends ImageUploadController
{
    private GalleryPictureRepositoryInterface $galleryPictureRepository;
    private Gallery $gallery;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->galleryPictureRepository = $container->galleryPictureRepository;
        $this->gallery = $container->gallery;
    }

    /**
     * Adds gallery picture to gallery author.
     */
    protected function addImage(array $context, Image $image, string $fileName) : void
    {
        $picture = GalleryPicture::create($context);

        $picture->comment = File::getName($fileName);
        $picture->pictureType = $image->imgType;
        $picture->thumbType = $image->imgType;

        $picture->publish();
        $picture->stamp($this->auth->getUser());

        $picture = $this->galleryPictureRepository->save($picture);

        // todo: get rid of getObj(), an interface must be used here
        $this->gallery->saveImage($picture->getObj(), $image);
    }
}
