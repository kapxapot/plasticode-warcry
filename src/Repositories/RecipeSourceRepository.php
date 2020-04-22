<?php

namespace App\Repositories;

use App\Models\RecipeSource;
use App\Repositories\Interfaces\RecipeSourceRepositoryInterface;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class RecipeSourceRepository extends IdiormRepository implements RecipeSourceRepositoryInterface
{
    public function get(?int $id): ?RecipeSource
    {
        return $this->getEntity($id);
    }
}
