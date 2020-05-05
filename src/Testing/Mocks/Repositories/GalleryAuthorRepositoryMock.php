<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\GalleryAuthorCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryAuthorCategory;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;
use Plasticode\Util\Strings;

class GalleryAuthorRepositoryMock implements GalleryAuthorRepositoryInterface
{
    private GalleryAuthorCollection $authors;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->authors = GalleryAuthorCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?GalleryAuthor
    {
        return $this
            ->authors
            ->first('id', $id);
    }

    public function getAllPublishedByCategory(
        GalleryAuthorCategory $category
    ) : GalleryAuthorCollection
    {
        return $this
            ->authors
            ->where(
                fn (GalleryAuthor $a) =>
                $a->isPublished()
                && $a->categoryId == $category->getId()
            );
    }

    public function getPublishedByAlias(string $alias) : ?GalleryAuthor
    {
        return $this
            ->authors
            ->first(
                fn (GalleryAuthor $a) =>
                $a->isPublished()
                && ($a->alias == $alias || $a->getId() == $alias)
            );
    }

    public function getPrev(GalleryAuthor $author) : ?GalleryAuthor
    {
        return $this
            ->authors
            ->where(
                fn (GalleryAuthor $a) => $a->isPublished()
            )
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
            ->authors
            ->where(
                fn (GalleryAuthor $a) => $a->isPublished()
            )
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
