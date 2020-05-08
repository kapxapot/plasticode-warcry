<?php

namespace App\Repositories;

use App\Collections\ComicStandalonePageCollection;
use App\Models\ComicStandalone;
use App\Models\ComicStandalonePage;
use App\Repositories\Interfaces\ComicStandalonePageRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\PublishedRepository;

class ComicStandalonePageRepository extends IdiormRepository implements ComicStandalonePageRepositoryInterface
{
    use PublishedRepository;

    protected string $entityClass = ComicStandalonePage::class;

    public function getAllByComic(
        ComicStandalone $comic
    ) : ComicStandalonePageCollection
    {
        return ComicStandalonePageCollection::from(
            $this
                ->publishedQuery()
                ->where(
                    ComicStandalonePage::comicIdField(),
                    $comic->getId()
                )
        );
    }
}
