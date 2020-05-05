<?php

namespace App\Repositories\Interfaces;

use App\Models\Item;

interface ItemRepositoryInterface
{
    function get(?int $id) : ?Item;
    function create(array $data) : Item;
    function save(Item $item) : Item;
}
