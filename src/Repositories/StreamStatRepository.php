<?php

namespace App\Repositories;

use App\Collections\StreamStatCollection;
use App\Models\Stream;
use App\Models\StreamStat;
use App\Repositories\Interfaces\StreamStatRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Util\Date;

class StreamStatRepository extends IdiormRepository implements StreamStatRepositoryInterface
{
    protected string $entityClass = StreamStat::class;

    public function save(StreamStat $stat) : StreamStat
    {
        return $this->saveEntity($stat);
    }

    public function getLastByStream(Stream $stream) : ?StreamStat
    {
        return $this
            ->byStreamQuery($stream)
            ->orderByDesc('created_at')
            ->one();
    }

    public function getLatestWithGameByStream(
        Stream $stream,
        int $days
    ) : StreamStatCollection
    {
        return StreamStatCollection::from(
            $this
                ->byStreamQuery($stream)
                ->whereRaw(
                    'created_at >= date_sub(now(), interval ' . $days . ' day) and length(remote_game) > 0'
                )
        );
    }

    public function getAllFromDateByStream(
        Stream $stream,
        \DateTime $from
    ) : StreamStatCollection
    {
        return StreamStatCollection::from(
            $this
                ->byStreamQuery($stream)
                ->whereGte('created_at', Date::formatDb($from))
                ->orderByAsc('created_at')
        );
    }

    // queries

    protected function byStreamQuery(Stream $stream) : Query
    {
        return $this
            ->query()
            ->where('stream_id', $stream->getId());
    }
}
