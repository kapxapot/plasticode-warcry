<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Menu;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\MenuItemRepositoryInterface;
use Plasticode\Hydrators\MenuHydrator as BaseMenuHydrator;
use Plasticode\Models\DbModel;

class MenuHydrator extends BaseMenuHydrator
{
    protected GameRepositoryInterface $gameRepository;
    protected MenuItemRepositoryInterface $menuItemRepository;

    protected LinkerInterface $linker;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        MenuItemRepositoryInterface $menuItemRepository,
        LinkerInterface $linker
    )
    {
        parent::__construct(
            $menuItemRepository,
            $linker
        );

        $this->gameRepository = $gameRepository;
    }

    /**
     * @param Menu $entity
     */
    public function hydrate(DbModel $entity) : Menu
    {
        /** @var Menu */
        $entity = parent::hydrate($entity);

        return $entity
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            );
    }
}
