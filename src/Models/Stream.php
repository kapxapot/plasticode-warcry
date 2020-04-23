<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tagged;
use Plasticode\Util\Cases;
use Plasticode\Util\Date;

class Stream extends DbModel
{
    use Description;
    use FullPublished;
    use Stamps;
    use Tagged;

    protected static string $sortField = 'remote_viewers';
    protected static bool $sortReverse = true;

    private bool $alive = false;

    public function alive() : bool
    {
        return $this->alive;
    }

    public function withAlive(bool $alive) : self
    {
        $this->alive = $alive;
        return $this;
    }

    // GETTERS - ONE
    
    public static function getPublishedByAlias($alias) : ?self
    {
        return self::getPublished()
            ->whereRaw(
                '(stream_alias = ? or (stream_alias is null and stream_id = ?))',
                [$alias, $alias]
            )
            ->one();
    }

    // PROPS
    
    public function game() : ?Game
    {
        return $this->lazy(
            function () {
                return $this->remoteGame
                    ? $this->gameRepository->getByTwitchName($this->remoteGame)
                    : null;
            }
        );
    }
    
    public function belongsToGame(Game $game) : bool
    {
        if (is_null($game) || is_null($this->game())) {
            return false;
        }
        
        return $game->root()->trunkContains($this->game());
    }
    
    public function priorityGame() : bool
    {
        return $this->official
            || $this->officialRu
            || Game::isPriority($this->remoteGame);
    }
    
    public function alias() : string
    {
        return $this->streamAlias ?? $this->streamId;
    }
    
    public function pageUrl() : string
    {
        return self::$container->linker->stream($this->alias());
    }
    
    public function imgUrl() : string
    {
        return self::$container->linker->twitchImg($this->streamId);
    }
    
    public function largeImgUrl() : string
    {
        return self::$container->linker->twitchLargeImg($this->streamId);
    }
    
    public function twitch() : bool
    {
        return true;
    }
    
    public function streamUrl() : string
    {
        return self::$container->linker->twitch($this->streamId);
    }
    
    public function verbs() : array
    {
        $form = [
            'time' => Cases::PAST,
            'person' => Cases::FIRST,
            'number' => Cases::SINGLE,
            'gender' => $this->genderId,
        ];

        $cases = self::$container->cases;

        return [
            'played' => $cases->conjugation('играть', $form),
            'broadcasted' => $cases->conjugation('транслировать', $form),
            'held' => $cases->conjugation('вести', $form),
        ];
    }
    
    public function nouns() : array
    {
        return [
            'viewers' => self::$container->cases->caseForNumber(
                'зритель', $this->remoteViewers
            ),
        ];
    }
    
    public function remoteOnlineAtIso() : ?string
    {
        return $this->remoteOnlineAt
            ? Date::iso($this->remoteOnlineAt)
            : null;
    }
    
    public function isOnline() : bool
    {
        return $this->remoteOnline == 1;
    }
    
    public function hasLogo() : bool
    {
        return strlen($this->remoteLogo) > 0;
    }

    public function displayRemoteStatus() : string
    {
        return urldecode($this->remoteStatus);
    }
}
