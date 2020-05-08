<?php

namespace App\Repositories;

use App\Collections\ComicIssueCollection;
use App\Models\ComicIssue;
use App\Models\ComicSeries;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

class ComicIssueRepository extends IdiormRepository implements ComicIssueRepositoryInterface
{
    use FullPublishedRepository;

    protected string $entityClass = ComicIssue::class;

    public function get(?int $id) : ?ComicIssue
    {
        return $this->getEntity($id);
    }

    /**
     * Returns all published comic issues by series.
     */
    public function getAllBySeries(ComicSeries $series) : ComicIssueCollection
    {
        return ComicIssueCollection::from(
            $this
                ->publishedQuery()
                ->where('series_id', $series->getId())
        );
    }
}
