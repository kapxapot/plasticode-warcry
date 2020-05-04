<?php

namespace App\Models;

use App\Collections\ForumCollection;
use App\Collections\GameCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\Parented;
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
 * @method GameCollection children()
 * @method ForumCollection forums()
 * @method bool isDefault()
 * @method Forum|null mainForum()
 * @method Forum|null newsForum()
 * @method string|null resultAlias()
 * @method string|null resultIcon()
 * @method static withChildren(GameCollection|callable $children)
 * @method static withForums(ForumCollection|callable $forums)
 * @method static withIsDefault(bool|callable $isDefault)
 * @method static withMainForum(Forum|callable|null $mainForum)
 * @method static withNewsForum(Forum|callable|null $newsForum)
 * @method static withResultAlias(string|callable|null $resultAlias)
 * @method static withResultIcon(string|callable|null $resultIcon)
 */
class Game extends DbModel implements LinkableInterface
{
    use Linkable;
    use Parented;
    use Published;

    protected function requiredWiths(): array
    {
        return [
            $this->childrenPropertyName,
            $this->parentPropertyName,
            $this->urlPropertyName,
            'forums',
            'mainForum',
            'newsForum',
            'resultAlias',
            'resultIcon',
        ];
    }

    /**
     * Child games WITH current game.
     */
    public function subTree() : GameCollection
    {
        return $this->subGames()->add($this);
    }

    /**
     * Child games with all their subtrees.
     */
    public function subGames() : GameCollection
    {
        return GameCollection::from(
            $this
                ->children()
                ->flatMap(
                    fn (Game $g) => $g->subTree()
                )
        );
    }

    /**
     * Checks if the current game relates to a given game.
     * 
     * If the games have the same root, they are related.
     */
    public function relatesToGame(Game $game) : bool
    {
        return $this
            ->root()
            ->equals(
                $game->root()
            );
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

    // LinkableInterface
    // implemented in Linkable trait
}
