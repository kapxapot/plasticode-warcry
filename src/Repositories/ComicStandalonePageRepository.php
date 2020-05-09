<?php

namespace App\Repositories;

use App\Collections\ComicStandalonePageCollection;
use App\Models\Comic;
use App\Models\ComicStandalone;
use App\Models\ComicStandalonePage;
use App\Repositories\Interfaces\ComicStandalonePageRepositoryInterface;

class ComicStandalonePageRepository extends ComicPageRepository implements ComicStandalonePageRepositoryInterface
{
    protected string $entityClass = ComicStandalonePage::class;

    protected function comicIdField(): string
    {
        return ComicStandalonePage::comicIdField();
    }

    /**
     * @param ComicStandalone $comic
     */
    public function getAllByComic(Comic $comic) : ComicStandalonePageCollection
    {
        return ComicStandalonePageCollection::from(
            parent::getAllByComic($comic)
        );
    }
}
