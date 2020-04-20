<?php

namespace App\Repositories;

use App\Collections\GameCollection;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\PublishedRepository;

class GameRepository extends IdiormRepository implements GameRepositoryInterface
{
    use PublishedRepository;

    protected $entityClass = Game::class;

    public function get(?int $id) : ?Game
    {
        return $this->getEntity($id);
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

    public function getByName(string $name) : ?Game
    {
        return $this
            ->query()
            ->where('name', $name)
            ->one();
    }
}
