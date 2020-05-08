<?php

namespace App\Repositories;

use App\Collections\ComicPageCollection;
use App\Models\ComicIssue;
use App\Models\ComicPage;
use App\Repositories\Interfaces\ComicPageRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\PublishedRepository;

class ComicPageRepository extends IdiormRepository implements ComicPageRepositoryInterface
{
    use PublishedRepository;

    protected string $entityClass = ComicPage::class;

    public function getAllByComic(ComicIssue $comic) : ComicPageCollection
    {
        return ComicPageCollection::from(
            $this
                ->publishedQuery()
                ->where(
                    ComicPage::comicIdField(),
                    $comic->getId()
                )
        );
    }
}
