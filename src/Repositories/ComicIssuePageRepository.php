<?php

namespace App\Repositories;

use App\Collections\ComicIssuePageCollection;
use App\Models\Comic;
use App\Models\ComicIssue;
use App\Models\ComicIssuePage;
use App\Repositories\Interfaces\ComicIssuePageRepositoryInterface;

class ComicIssuePageRepository extends ComicPageRepository implements ComicIssuePageRepositoryInterface
{
    protected string $entityClass = ComicIssuePage::class;

    protected function comicIdField(): string
    {
        return ComicIssuePage::comicIdField();
    }

    /**
     * @param ComicIssue $comic
     */
    public function getAllByComic(Comic $comic) : ComicIssuePageCollection
    {
        return ComicIssuePageCollection::from(
            parent::getAllByComic($comic)
        );
    }
}
