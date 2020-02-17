<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Util\Date;

class StreamStat extends DbModel
{
    public static function fromStream(Stream $stream) : self
    {
        return new static([
            'stream_id' => $stream->getId(),
            'remote_game' => $stream->remoteGame,
            'remote_viewers' => $stream->remoteViewers,
            'remote_status' => $stream->remoteStatus,
        ]);
    }

    // getters - one
    
    public static function getLast($streamId)
    {
        return self::query()
            ->where('stream_id', $streamId)
            ->orderByDesc('created_at')
            ->one();
    }

    // methods

    public function finish()
    {
        $this->finishedAt = Date::dbNow();
        $this->save();
    }
    
    // getters - many
    
    public static function getGames($streamId, $days = 30) : Collection
    {
        $table = static::getTable();

        return self::query()
            ->rawQuery(
                "select remote_game, count(*) count
                from {$table}
                where created_at >= date_sub(now(), interval {$days} day) and length(remote_game) > 0 and stream_id = :stream_id
                group by remote_game",
                [
                    'stream_id' => intval($streamId),
                ]
            )
            ->all();
    }
    
    public static function getFrom($streamId, \DateTime $from) : Collection
    {
        return self::query()
            ->where('stream_id', $streamId)
            ->whereGte('created_at', Date::formatDb($from))
            ->orderByAsc('created_at')
            ->all();
    }
    
    // props
    
    public function displayRemoteStatus()
    {
        return urldecode($this->remoteStatus);
    }
}
