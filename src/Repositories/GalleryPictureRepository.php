<?php

namespace App\Repositories;

use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\TagsRepository;

class GalleryPictureRepository extends IdiormRepository implements GalleryPictureRepositoryInterface
{
    use TagsRepository;

    protected $entityClass = GalleryPicture::class;

    public function get(int $id) : ?GalleryPicture
    {
        return $this->getEntity($id);
    }

    public function getByTag(string $tag, int $limit = null) : Collection
    {
        $query = $this->getByTagQuery(
            $this->tagRepository,
            $this->query(),
            $tag
        );

        return $query->limit($limit)->all();
    }
}
