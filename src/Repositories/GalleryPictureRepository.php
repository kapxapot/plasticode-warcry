<?php

namespace App\Repositories;

use App\Models\GalleryPicture;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Data\Db;
use Plasticode\Repositories\Idiorm\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\Tags;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class GalleryPictureRepository extends IdiormRepository implements GalleryPictureRepositoryInterface
{
    use Tags;

    /** @var TagRepositoryInterface */
    private $tagRepository;

    public function __construct(Db $db, TagRepositoryInterface $tagRepository)
    {
        parent::__construct($db);

        $this->tagRepository = $tagRepository;
    }

    public function get(int $id) : ?GalleryPicture
    {
        return GalleryPicture::get($id);
    }

    public function getByTag(string $tag, int $limit = null) : Collection
    {
        $query = $this->getByTagQuery(
            $this->tagRepository,
            GalleryPicture::query(),
            $tag
        );

        return $query->limit($limit)->all();
    }
}
