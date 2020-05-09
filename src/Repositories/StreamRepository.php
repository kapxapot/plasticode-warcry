<?php

namespace App\Repositories;

use App\Collections\StreamCollection;
use App\Models\Stream;
use App\Repositories\Interfaces\StreamRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;

class StreamRepository extends TaggedRepository implements StreamRepositoryInterface
{
    use FullPublishedRepository;

    protected string $entityClass = Stream::class;

    protected string $sortField = 'remote_viewers';
    protected bool $sortReverse = true;

    public function save(Stream $stream) : Stream
    {
        return $this->saveEntity($stream);
    }

    public function getPublishedByAlias(string $alias) : ?Stream
    {
        return $this
            ->publishedQuery()
            ->whereRaw(
                '(stream_alias = ? or (stream_alias is null and stream_id = ?))',
                [$alias, $alias]
            )
            ->one();
    }

    public function getAllPublished() : StreamCollection
    {
        return StreamCollection::from(
            $this->publishedQuery()
        );
    }

    public function getAllByTag(string $tag, int $limit = 0) : StreamCollection
    {
        return StreamCollection::from(
            $this->filterByTag($this->publishedQuery(), $tag, $limit)
        );
    }
}
