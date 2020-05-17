<?php

namespace App\Repositories;

use App\Collections\ComicStandaloneCollection;
use App\Models\ComicStandalone;
use App\Repositories\Interfaces\ComicStandaloneRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

class ComicStandaloneRepository extends TaggedRepository implements ComicStandaloneRepositoryInterface
{
    use FullPublishedRepository;

    protected string $entityClass = ComicStandalone::class;

    protected string $sortField = 'issued_on';
    protected bool $sortReverse = true;

    public function get(?int $id) : ?ComicStandalone
    {
        return $this->getEntity($id);
    }

    public function getAllPublished() : ComicStandaloneCollection
    {
        return ComicStandaloneCollection::from(
            $this->publishedQuery()
        );
    }

    public function getPublishedByAlias(string $alias) : ?ComicStandalone
    {
        return $this
            ->publishedQuery()
            ->where('alias', $alias)
            ->one();
    }

    public function getAllByTag(
        string $tag,
        int $limit = 0
    ) : ComicStandaloneCollection
    {
        return ComicStandaloneCollection::from(
            $this->filterByTag($this->publishedQuery(), $tag, $limit)
        );
    }
}
