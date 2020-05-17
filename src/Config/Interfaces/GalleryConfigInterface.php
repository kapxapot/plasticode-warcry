<?php

namespace App\Config\Interfaces;

interface GalleryConfigInterface
{
    /**
     * Number of pictures by page in gallery.
     */
    function galleryPicsPerPage() : int;
}
