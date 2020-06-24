<?php

namespace App\Repositories\Interfaces;

use App\Collections\NewsSourceCollection;
use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Repositories\Interfaces\Basic\SearchableNewsSourceRepositoryInterface as BaseSearchableNewsSourceRepositoryInterface;

interface SearchableNewsSourceRepositoryInterface extends BaseSearchableNewsSourceRepositoryInterface
{
    function getProtected(?int $id) : ?NewsSourceInterface;
    function search(string $searchQuery) : NewsSourceCollection;
}
