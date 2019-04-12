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
	
	public static function getPublishedByAlias($alias)
	{
		return self::getPublished()
		    ->whereRaw(
		        '(stream_alias = ? or (stream_alias is null and stream_id = ?))',
		        [ $alias, $alias ]
            )
		    ->one();
	}

    // PROPS
    
    public function alive()
    {
        if (!$this->remoteOnlineAt) {
            return false;
        }
        
		$timeToLive = self::getSettings('streams.ttl');
		$age = Date::age($this->remoteOnlineAt);
			
		return $age->days < $timeToLive;
    }
    
    public function game()
    {
        return $this->lazy(__FUNCTION__, function () {
            return $this->remoteGame
                ? Game::getByTwitchName($this->remoteGame)
                : null;
        });
    }
    
    public function belongsToGame($game)
    {
        //var_dump([ $this->remoteGame, $this->game()->toString(), $game->toString() ]);
        
        if (is_null($game) || is_null($this->game())) {
            return false;
        }
        
        return $game->root()->subTreeContains($this->game());
    }
    
    public function priorityGame()
    {
        return $this->official || $this->officialRu || Game::isPriority($this->remoteGame);
    }
    
    public function alias()
    {
        return $this->streamAlias ?? $this->streamId;
    }
    
    public function pageUrl()
    {
        return self::$linker->stream($this->alias());
    }
    
    public function imgUrl()
    {
        return self::$linker->twitchImg($this->streamId);
    }
    
    public function largeImgUrl()
    {
        return self::$linker->twitchLargeImg($this->streamId);
    }
    
    public function twitch()
    {
        return true;
    }
    
    public function streamUrl()
    {
        return self::$linker->twitch($this->streamId);
    }
    
    public function verbs()
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
    
    public function nouns()
    {
        return [
            'viewers' => self::$cases->caseForNumber('зритель', $this->remoteViewers),
        ];
    }
    
    public function remoteOnlineAtIso()
    {
        return $this->remoteOnlineAt
            ? Date::iso($this->remoteOnlineAt)
            : null;
    }
    
    public function isOnline()
    {
        return $this->remoteOnline == 1;
    }
    
    public function hasLogo()
    {
        return strlen($this->remoteLogo) > 0;
    }

	public function displayRemoteStatus()
	{
	    return urldecode($this->remoteStatus);
	}
}
