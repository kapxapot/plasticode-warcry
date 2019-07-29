<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Cases;
use Plasticode\Util\Date;

class Stream extends DbModel
{
    use Description, FullPublish, Stamps, Tags;

    protected static $sortField = 'remote_viewers';
    protected static $sortReverse = true;

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
    
    public function alive() : bool
    {
        if (!$this->remoteOnlineAt) {
            return false;
        }
        
        $timeToLive = self::getSettings('streams.ttl');
        $age = Date::age($this->remoteOnlineAt);
            
        return $age->days < $timeToLive;
    }
    
    public function game() : ?Game
    {
        return $this->lazy(
            function () {
                return $this->remoteGame
                    ? Game::getByTwitchName($this->remoteGame)
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
        return self::$linker->stream($this->alias());
    }
    
    public function imgUrl() : string
    {
        return self::$linker->twitchImg($this->streamId);
    }
    
    public function largeImgUrl() : string
    {
        return self::$linker->twitchLargeImg($this->streamId);
    }
    
    public function twitch() : bool
    {
        return true;
    }
    
    public function streamUrl() : string
    {
        return self::$linker->twitch($this->streamId);
    }
    
    public function verbs() : array
    {
        $form = [
            'time' => Cases::PAST,
            'person' => Cases::FIRST,
            'number' => Cases::SINGLE,
            'gender' => $this->genderId,
        ];
        
        return [
            'played' => self::$cases->conjugation('играть', $form),
            'broadcasted' => self::$cases->conjugation('транслировать', $form),
            'held' => self::$cases->conjugation('вести', $form),
        ];
    }
    
    public function nouns() : array
    {
        return [
            'viewers' => self::$cases->caseForNumber(
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
