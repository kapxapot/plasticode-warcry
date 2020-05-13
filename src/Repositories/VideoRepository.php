<?php

namespace App\Repositories;

use App\Collections\VideoCollection;
use App\Models\Video;
use App\Repositories\Interfaces\VideoRepositoryInterface;
use Plasticode\Query;

class VideoRepository extends NewsSourceRepository implements VideoRepositoryInterface
{
    protected string $entityClass = Video::class;

    public function get(?int $id) : ?Video
    {
        return $this->getEntity($id);
    }

    public function getProtected(?int $id) : ?Video
    {
        return $this->getProtectedEntity($id);
    }

    // SearchableRepositoryInterface

    public function search(string $searchQuery) : VideoCollection
    {
        return VideoCollection::from(
            $this
                ->publishedQuery()
                ->search($searchQuery, '(name like ?)')
                ->orderByAsc('name')
        );
    }

    // queries

    protected function newsSourceQuery() : Query
    {
        return $this->announcedQuery();
    }

    /**
     * Published + announced query.
     */
    protected function announcedQuery() : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterAnnounced($q)
            );
    }

    // filters

    protected function filterAnnounced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
}
