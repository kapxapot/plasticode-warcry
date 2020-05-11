<?php

namespace App\Repositories;

use App\Collections\MenuCollection;
use App\Models\Game;
use App\Models\Menu;
use App\Repositories\Interfaces\MenuRepositoryInterface;
use App\Repositories\Traits\ByGameRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\MenuRepository as BaseMenuRepository;

class MenuRepository extends BaseMenuRepository implements MenuRepositoryInterface
{
    use ByGameRepository;

    protected string $entityClass = Menu::class;

    public function getAll() : MenuCollection
    {
        return MenuCollection::from(
            $this->query()
        );
    }

    public function getAllByGame(?Game $game) : MenuCollection
    {
        return MenuCollection::from(
            $this
                ->query()
                ->apply(
                    fn (Query $q) => $this->filterByGame($q, $game)
                )
        );
    }
}
