<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CreatedAt;

/**
 * @property string|null $finishedAt
 * @property string|null $remoteGame
 * @property string|null $remoteStatus
 * @property integer $remoteViewers
 * @property integer $streamId
 */
class StreamStat extends DbModel
{
    use CreatedAt;

    public function fromStream(Stream $stream) : self
    {
        return new static(
            [
                'stream_id' => $stream->getId(),
                'remote_game' => $stream->remoteGame,
                'remote_viewers' => $stream->remoteViewers,
                'remote_status' => $stream->remoteStatus,
            ]
        );
    }

    public function displayRemoteStatus() : string
    {
        return urldecode($this->remoteStatus);
    }
}
