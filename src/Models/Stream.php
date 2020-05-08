<?php

namespace App\Models;

use App\Models\Traits\Description;
use App\Models\Traits\PageUrl;
use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\TaggedInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @property integer $channel
 * @property integer $genderId
 * @property integer $official
 * @property integer $officialRu
 * @property integer $priority
 * @property string|null $remoteGame
 * @property string|null $remoteLogo
 * @property integer $remoteOnline
 * @property string|null $remoteOnlineAt
 * @property string|null $remoteStatus
 * @property string|null $remoteTitle
 * @property string|null $remoteUpdatedAt
 * @property integer $remoteViewers
 * @property string|null $streamAlias
 * @property string $streamId
 * @property string $title
 * @method Game|null game()
 * @method string imgUrl()
 * @method bool isAlive()
 * @method bool isPriorityGame()
 * @method string largeImgUrl()
 * @method array nouns()
 * @method string streamUrl()
 * @method array verbs()
 * @method static withGame(Game|callable|null $game)
 * @method static withImgUrl(string|callable $imgUrl)
 * @method static withIsAlive(bool|callable $isAlive)
 * @method static withIsPriorityGame(bool|callable $isPriorityGame)
 * @method static withLargeImgUrl(string|callable $largeImgUrl)
 * @method static withNouns(array|callable $nouns)
 * @method static withStreamUrl(string|callable $streamUrl)
 * @method static withVerbs(array|callable $verbs)
 */
class Stream extends DbModel implements TaggedInterface
{
    use Description;
    use FullPublished;
    use PageUrl;
    use Stamps;
    use Tagged;

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
            $this->pageUrlPropertyName,
            $this->parsedDescriptionPropertyName,
            $this->updaterPropertyName,
            $this->tagLinksPropertyName,
            'game',
            'imgUrl',
            'isAlive',
            'isPriorityGame',
            'largeImgUrl',
            'nouns',
            'streamUrl',
            'verbs',
        ];
    }

    /**
     * Checks if the stream's game relates to a given game.
     */
    public function relatesToGame(Game $game) : bool
    {
        return
            $game
            && $this->game()
            && $this->game()->relatesToGame($game);
    }

    public function isTop() : bool
    {
        return $this->official
            || $this->officialRu
            || $this->isPriorityGame();
    }

    public function alias() : string
    {
        return $this->streamAlias ?? $this->streamId;
    }

    public function remoteOnlineAtIso() : ?string
    {
        return self::toIso($this->remoteOnlineAt);
    }

    public function isOnline() : bool
    {
        return self::toBool($this->remoteOnline);
    }

    public function hasLogo() : bool
    {
        return strlen($this->remoteLogo) > 0;
    }

    public function displayRemoteStatus() : string
    {
        return urldecode($this->remoteStatus);
    }

    public function isChannel() : bool
    {
        return self::toBool($this->channel);
    }

    public function isOfficial() : bool
    {
        return self::toBool($this->official);
    }

    public function isOfficialRu() : bool
    {
        return self::toBool($this->officialRu);
    }
}
