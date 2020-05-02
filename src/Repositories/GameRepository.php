<?php

namespace App\Repositories;

use App\Collections\GameCollection;
use App\Config\Interfaces\GameConfigInterface;
use App\Models\Forum;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Idiorm\Traits\PublishedRepository;

class GameRepository extends IdiormRepository implements GameRepositoryInterface
{
    use PublishedRepository;

    protected string $entityClass = Game::class;

    private GameConfigInterface $config;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $repositoryContext,
        GameConfigInterface $config,
        $hydrator = null
    )
    {
        parent::__construct($repositoryContext, $hydrator);

        $this->config = $config;
    }

    public function get(?int $id) : ?Game
    {
        return $this->getEntity($id);
    }

    public function getDefault() : ?Game
    {
        $id = $this->config->defaultGameId();

        return $this->get($id);
    }

    public function getAll() : GameCollection
    {
        return GameCollection::from(
            $this->query()
        );
    }

    public function getAllPublished() : GameCollection
    {
        return GameCollection::from(
            $this->publishedQuery()
        );
    }

    public function getPublishedByAlias(string $alias) : ?Game
    {
        return $this
            ->publishedQuery()
            ->where('alias', $alias)
            ->one();
    }

    public function getByName(?string $name) : ?Game
    {
        if (strlen($name) == 0) {
            return null;
        }

        return $this
            ->query()
            ->where('name', $name)
            ->one();
    }

    public function getByTwitchName(?string $name) : ?Game
    {
        return $this->getByName($name) ?? $this->getDefault();
    }

    /**
     * Returns game by forum (going up in the forum tree).
     * If not found returns the default game.
     */
    public function getByForum(Forum $forum) : ?Game
    {
        return $this
            ->getAll()
            ->first(
                fn (Game $g) => $forum->belongsToGame($g)
            )
            ?? $this->getDefault();
    }

    /**
     * Returns game's sub-tree or all games (if the game is null).
     */
    public function getSubTreeOrAll(?Game $game) : GameCollection
    {
        return $game
            ? $game->subTree()
            : $this->getAll();
    }
}
