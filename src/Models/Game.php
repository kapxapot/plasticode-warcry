<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\Children;
use Plasticode\Models\Traits\Publish;

/**
 * @property string|null $alias
 */
class Game extends DbModel implements LinkableInterface
{
    use Children, Publish;
    
    protected static $sortField = 'position';

    // GETTERS - ONE

    public static function getPublishedByAlias(string $alias) : ?self
    {
        return self::getPublished()
            ->where('alias', $alias)
            ->one();
    }

    public static function getByTwitchName(string $name) : ?self
    {
        return self::query()
            ->where('name', $name)
            ->one()
            
            ?? self::getDefault();
    }

    public static function getByForumId(int $forumId) : ?self
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
                    if ($game->newsForumId == $curForumId
                        || $game->mainForumId == $curForumId) {
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
    
    public static function getDefaultId() : ?int
    {
        return self::getSettings('default_game_id');
    }

    public static function getDefault() : ?self
    {
        $id = self::getDefaultId();
        return static::get($id);
    }
    
    public static function getRootOrDefault(int $id) : ?self
    {
        $game = self::get($id);
        
        return $game
            ? $game->root()
            : self::getDefault();
    }

    // PROPS
    
    /**
     * The ultimate parent id
     *
     * @return integer
     */
    public function rootId() : int
    {
        return $this->lazy(
            function () {
                return $this->parent()
                    ? $this->parent()->rootId()
                    : $this->id;
            }
        );
    }

    /**
     * The ultimate parent game
     *
     * @return Game
     */
    public function root() : self
    {
        return $this->lazy(
            function () {
                return self::get($this->rootId());
            }
        );
    }
    
    /**
     * Child games
     *
     * @return Collection
     */
    public function subGames() : Collection
    {
        return $this->lazy(
            function () {
                $subGames = Collection::make([$this]);

                foreach ($this->children() as $child) {
                    $subGames = $subGames->concat($child->subGames());
                }
                
                return $subGames;
            }
        );
    }
    
    /**
     * Child games WITH current game.
     *
     * @return Collection
     */
    public function subTree() : Collection
    {
        return $this->subGames()->add($this);
    }
    
    /**
     * Any of parents contains game?
     *
     * @param self $game
     * @return boolean
     */
    public function trunkContains(self $game) : bool
    {
        while ($game) {
            if ($game->id == $this->id) {
                return true;
            }
            
            $game = $game->parent();
        }
        
        return false;
    }
    
    public function default() : bool
    {
        return $this->id == self::getDefaultId();
    }
    
    public function url() : ?string
    {
        return self::$container->linker->game($this);
    }
    
    public function resultIcon() : ?string
    {
        return $this->icon
            ??
            ($this->parent()
                ? $this->parent()->resultIcon()
                : null)
            ??
            (self::getDefault()
                ? self::getDefault()->resultIcon()
                : null);
    }
    
    public function resultAlias() : ?string
    {
        return $this->alias
            ??
            ($this->parent()
                ? $this->parent()->resultAlias()
                : null)
            ??
            (self::getDefault()
                ? self::getDefault()->resultAlias()
                : null);
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
    
    public static function isPriority($gameName) : bool
    {
        $priorityGames = self::getSettings('streams.priority_games');

        return in_array(strtolower($gameName), $priorityGames);
    }
    
    public static function getNewsForumIds(self $game = null) : Collection
    {
        $games = $game
            ? $game->subTree()
            : self::getAll();

        return $games->extract('news_forum_id');
    }

    public function toString() : string
    {
        return "[{$this->id}] {$this->name}";
    }
}
