<?php

namespace App\Repositories;

use App\Config\Interfaces\GameConfigInterface;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Data\Db;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\Publish;

class GameRepository extends IdiormRepository implements GameRepositoryInterface
{
    use Publish;

    protected $entityClass = Game::class;

    /** @var GameConfigInterface */
    private $config;

    public function __construct(
        Db $db,
        GameConfigInterface $config
    )
    {
        parent::__construct($db);

        $this->config = $config;
    }

    public function get(int $id) : ?Game
    {
        return $this->getEntity($id);
    }

    public function getDefault() : ?Game
    {
        $id = $this->config->defaultGameId();

        return $this->get($id);
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

    public function getByTwitchName(string $name) : ?Game
    {
        return $this->getByTwitchName($name) ?? $this->getDefault();
    }
}
