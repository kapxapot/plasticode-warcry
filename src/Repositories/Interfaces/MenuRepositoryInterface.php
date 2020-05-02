<?php

namespace App\Repositories\Interfaces;

use App\Collections\MenuCollection;
use App\Models\Game;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface as BaseMenuRepoositoryInterface;

interface MenuRepositoryInterface extends BaseMenuRepoositoryInterface
{
    function getAll() : MenuCollection;
    function getAllByGame(?Game $game) : MenuCollection;
}
