<?php

namespace App\Repositories;

use App\Collections\GalleryAuthorCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryAuthorCategory;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\PublishedRepository;
use Plasticode\Util\Strings;

class GalleryAuthorRepository extends IdiormRepository implements GalleryAuthorRepositoryInterface
{
    use PublishedRepository;

    protected string $entityClass = GalleryAuthor::class;

    public function get(?int $id) : ?GalleryAuthor
    {
        return $this->getEntity($id);
    }

    public function getAllPublishedByCategory(
        GalleryAuthorCategory $category
    ) : GalleryAuthorCollection
    {
        return GalleryAuthorCollection::from(
            $this
                ->publishedQuery()
                ->where('category_id', $category->getId())
        );
    }

    public function getPublishedByAlias(string $alias) : ?GalleryAuthor
    {
        return $this
            ->publishedQuery()
            ->whereAnyIs(
                [
                    ['alias' => $alias],
                    ['id' => $alias],
                ]
            )
            ->one();
    }

    public function getPrev(GalleryAuthor $author) : ?GalleryAuthor
    {
        return $this
            ->publishedQuery()
            ->all()
            ->descStr(
                fn (GalleryAuthor $a) => $a->displayName()
            )
            ->first(
                fn (GalleryAuthor $a) =>
                Strings::compare(
                    $a->displayName(),
                    $author->displayName()
                ) < 0
            );
    }

    public function getNext(GalleryAuthor $author) : ?GalleryAuthor
    {
        return $this
            ->publishedQuery()
            ->all()
            ->ascStr(
                fn (GalleryAuthor $a) => $a->displayName()
            )
            ->first(
                fn (GalleryAuthor $a) =>
                Strings::compare(
                    $a->displayName(),
                    $author->displayName()
                ) > 0
            );
    }
}
