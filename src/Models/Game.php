<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Children;
use Plasticode\Models\Traits\Publish;

class Game extends DbModel
{
    use Children, Publish;
    
    protected static $sortField = 'position';

	// GETTERS - ONE

	public static function getPublishedByAlias($alias)
	{
		return self::getPublishedWhere(function ($q) use ($alias) {
    		return $q->where('alias', $alias);
		});
	}

	public static function getByTwitchName($name)
	{
		return self::getBy(function ($q) use ($name) {
    		return $q->where('name', $name);
		}) ?? self::getDefault();
	    
    	/*$game = $this->getBy(Tables::GAMES, function($q) use ($name) {
    		return $q->whereRaw('(coalesce(twitch_name, name) = ?)', [ $name ]);
    	});
    	
    	return $game ? $this->getGame($game['id']) : null;*/
	}

	public static function getByForumId($forumId)
	{
	    $cache = self::$container->cache;
	    
		$path = 'gamesByForumId.' . $forumId;
		$games = $cache->get($path);
		
		if (!$games) {
			$games = static::getAllPublished();
			$foundGame = null;

			$curForumId = $forumId;
			
			while (!$foundGame && $curForumId != -1) {
				foreach ($games as $game) {
					if ($game->newsForumId == $curForumId || $game->mainForumId == $curForumId) {
						$foundGame = $game;
						break;
					}
				}

				if (!$foundGame) {
					$forum = Forum::get($curForumId);
					$curForumId = $forum->parentId;
				}
			}

			$cache->set($path, $foundGame ?? self::getDefault());
		}

		return $cache->get($path);
	}
	
	public static function getDefaultId()
	{
		return self::getSettings('default_game_id');
	}

	public static function getDefault()
	{
		$id = self::getDefaultId();
		return static::get($id);
	}
	
	public static function getRootOrDefault($id)
	{
	    $game = self::get($id);
	    
	    return $game
	        ? $game->root()
	        : self::getDefault();
	}

	// PROPS
	
	public function rootId()
	{
	    return $this->lazy(__FUNCTION__, function () {
	        return $this->parent()
	            ? $this->parent()->rootId()
	            : $this->id;
	    });
	}

	public function root()
	{
	    return $this->lazy(__FUNCTION__, function () {
	        return self::get($this->rootId());
	    });
	}
	
	public function subGames()
	{
	    return $this->lazy(__FUNCTION__, function () {
    	    $subGames = [ $this ];
    	    $children = $this->children();
    	    
    	    foreach ($children as $child) {
    	        $subGames = array_merge($subGames, $child->subGames()->toArray());
    	    }
    	    
    	    return Collection::make($subGames);
	    });
	}
	
	public function subTree()
	{
	    return $this->subGames()->add($this);
	}
	
	public function subTreeContains($game)
	{
	    while ($game) {
	        if ($game->id == $this->id) {
	            return true;
	        }
	        
	        $game = $game->parent();
	    }
	    
	    return false;
	}
	
	public function default()
	{
		return $this->id == self::getDefaultId();
	}
	
	public function url()
	{
	    return self::$linker->game($this);
	}
	
	public function resultIcon()
	{
	    return $this->icon ?? ($this->parent() ? $this->parent()->resultIcon() : null);
	}
	
	public function resultAlias()
	{
	    return $this->alias ?? ($this->parent() ? $this->parent()->resultAlias() : null);
	}
	
	public function forums()
	{
	    return Forum::getAllByGame($this->getId());
	}

    // FUNCS

	public function filter($query)
	{
	    $ids = $this->subTree()->ids();

		return $query->whereIn('game_id', $ids->toArray());
	}
	
	public static function isPriority($gameName)
	{
		$priorityGames = self::getSettings('streams.priority_games');

		return in_array(strtolower($gameName), $priorityGames);
	}
	
	public static function getNewsForumIds($game = null) {
	    $games = $game
	        ? $game->subTree()
	        : self::getAll();

		return $games->extract('news_forum_id');
	}

    public function __toString()
    {
        return "[{$this->id}] {$this->name}";
    }
}
