<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
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
		return self::getPublished()
    		->where('alias', $alias)
    		->one();
	}

	public static function getByTwitchName($name)
	{
		return self::query()
    		->where('name', $name)
    		->one()
    		
    		?? self::getDefault();
	}

	public static function getByForumId($forumId)
	{
	    $cache = self::$container->cache;
	    
		$path = 'gamesByForumId.' . $forumId;
		$games = $cache->get($path);
		
		if (!$games) {
			$games = static::getPublished()->all();
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
	
	public function subGames() : Collection
	{
	    return $this->lazy(__FUNCTION__, function () {
    	    $subGames = Collection::make([ $this ]);

    	    foreach ($this->children() as $child) {
    	        $subGames = $subGames->concat($child->subGames());
    	    }
    	    
    	    return $subGames;
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
	
	public function forums() : Collection
	{
	    return Forum::getAllByGame($this->getId());
	}

    // FUNCS

	public function filter(Query $query) : Query
	{
	    $ids = $this->subTree()->ids();

		return $query->whereIn('game_id', $ids);
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

    public function toString()
    {
        return "[{$this->id}] {$this->name}";
    }
}
