<?php

namespace App\Repositories;

use App\Collections\ComicIssueCollection;
use App\Models\ComicIssue;
use App\Models\ComicSeries;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

class ComicIssueRepository extends TaggedRepository implements ComicIssueRepositoryInterface
{
    use FullPublishedRepository;

    protected string $entityClass = ComicIssue::class;

    protected string $sortField = 'number';

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

    public function getAllByTag(string $tag, int $limit = 0) : ComicIssueCollection
    {
        return ComicIssueCollection::from(
            $this->filterByTag($this->publishedQuery(), $tag, $limit)
        );
    }
}
