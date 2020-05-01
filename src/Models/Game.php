<?php

namespace App\Models;

use App\Collections\GameCollection;
use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\Published;

/**
 * @property string|null $alias
 * @property string|null $autotags
 * @property string|null $icon
 * @property integer|null $mainForumId
 * @property string $name
 * @property integer|null $newsForumId
 * @property integer|null $parentId
 * @property integer|null $position
 * @property string|null $twitchName
 * @method Forum|null mainForum()
 * @method Forum|null newsForum()
 * @method static withMainForum(Forum|callable|null $mainForum)
 * @method static withNewsForum(Forum|callable|null $newsForum)
 */
class Game extends DbModel implements LinkableInterface
{
    use Published;

    protected static string $sortField = 'position';

    // GETTERS - ONE
    
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
     * Child games with all their subtrees.
     */
    public function subGames() : GameCollection
    {
        $games = GameCollection::make();

        foreach ($this->children() as $child) {
            $games = $games->concat(
                $child->subTree()
            );
        }

        return $games;
    }

    /**
     * Child games WITH current game.
     */
    public function subTree() : GameCollection
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

    /**
     * Checks if the game relates to forum
     * (the forum is defined as main forum or news forum for the game).
     */
    public function relatesToForum(Forum $forum) : bool
    {
        return
            $forum->equals($this->newsForum())
            || $forum->equals($this->mainForum());
    }

    public function toString() : string
    {
        return '[' . $this->getId() . '] ' . $this->name;
    }
}
