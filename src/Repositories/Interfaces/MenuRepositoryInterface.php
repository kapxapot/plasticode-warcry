<?php

namespace App\Repositories\Interfaces;

use App\Collections\MenuCollection;
use App\Models\Game;
use App\Models\Menu;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface as BaseMenuRepositoryInterface;

interface MenuRepositoryInterface extends BaseMenuRepositoryInterface
{
    function get(?int $id) : ?Menu;
    function getAll() : MenuCollection;
    function getAllByGame(?Game $game) : MenuCollection;
}
