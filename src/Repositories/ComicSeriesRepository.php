<?php

namespace App\Repositories;

use App\Collections\ComicSeriesCollection;
use App\Models\ComicSeries;
use App\Repositories\Interfaces\ComicSeriesRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

class ComicSeriesRepository extends TaggedRepository implements ComicSeriesRepositoryInterface
{
    use FullPublishedRepository;

    protected string $entityClass = ComicSeries::class;

    public function get(?int $id) : ?ComicSeries
    {
        return $this->getEntity($id);
    }

    public function getAllPublished() : ComicSeriesCollection
    {
        return ComicSeriesCollection::from(
            $this->publishedQuery()
        );
    }

    public function getPublishedByAlias(string $alias) : ?ComicSeries
    {
        return $this
            ->publishedQuery()
            ->where('alias', $alias)
            ->one();
    }
}
