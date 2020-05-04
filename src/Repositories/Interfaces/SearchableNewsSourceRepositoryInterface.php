<?php

namespace App\Repositories\Interfaces;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Repositories\Interfaces\Basic\ProtectedRepositoryInterface;
use Plasticode\Repositories\Interfaces\Basic\SearchableRepositoryInterface;

interface SearchableNewsSourceRepositoryInterface extends NewsSourceRepositoryInterface, ProtectedRepositoryInterface, SearchableRepositoryInterface
{
    function getProtected(?int $id) : ?NewsSourceInterface;
}
