<?php

namespace App\Tests\Mocks\Repositories;

use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Tests\Seeders\Interfaces\ArraySeederInterface;
use Plasticode\Util\Strings;

class GalleryPictureRepositoryMock implements GalleryPictureRepositoryInterface
{
    /** @var Collection */
    private $pictures;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->pictures = Collection::make($seeder->seed());
    }

    public function get(int $id) : ?GalleryPicture
    {
        return $this
            ->pictures
            ->where('id', $id)
            ->first();
    }

    public function getByTag(string $tag, int $limit = null) : Collection
    {
        $pictures = $this
            ->pictures
            ->where(
                function (GalleryPicture $picture) use ($tag) {
                    $tags = Strings::toTags($picture->tags);
                    $normTag = Strings::normalize($tag);

                    return in_array($normTag, $tags);
                }
            );
        
        return $limit
            ? $pictures->take($limit)
            : $pictures;
    }
}
