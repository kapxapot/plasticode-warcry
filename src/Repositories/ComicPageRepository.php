<?php

namespace App\Repositories;

use App\Collections\ComicPageCollection;
use App\Models\Comic;
use App\Repositories\Interfaces\ComicPageRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\PublishedRepository;

abstract class ComicPageRepository extends IdiormRepository implements ComicPageRepositoryInterface
{
    use PublishedRepository;

    protected string $sortField = 'number';

    abstract protected function comicIdField() : string;

    public function getAllByComic(Comic $comic) : ComicPageCollection
    {
        return ComicPageCollection::from(
            $this
                ->publishedQuery()
                ->where(
                    $this->comicIdField(),
                    $comic->getId()
                )
        );
    }
}
