<?php

namespace App\Testing\Mocks\Repositories;

use App\Collections\GameCollection;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class GameRepositoryMock implements GameRepositoryInterface
{
    private GameCollection $games;

    public function __construct(ArraySeederInterface $seeder)
    {
        $this->games = GameCollection::make($seeder->seed());
    }

    public function get(?int $id) : ?Game
    {
        return $this->games->first('id', $id);
    }

    public function getDefault() : ?Game
    {
        return $this->games->first();
    }

    public function getAll() : GameCollection
    {
        return $this->games;
    }

    public function getAllPublished() : GameCollection
    {
        return $this
            ->games
            ->where(
                fn (Game $g) => $g->isPublished()
            );
    }

    public function getPublishedByAlias(string $alias) : ?Game
    {
        return $this
            ->getAllPublished()
            ->first('alias', $alias);
    }

    public function getByName(string $name) : ?Game
    {
        return $this->games->first('name', $name);
    }

    function getByTwitchName(string $name) : ?Game
    {
        return $this->getByName($name) ?? $this->getDefault();
    }
}
