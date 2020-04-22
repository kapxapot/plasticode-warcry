<?php

namespace App\Repositories;

use App\Collections\RecipeCollection;
use App\Models\Recipe;
use App\Repositories\Interfaces\RecipeRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;

class RecipeRepository extends IdiormRepository implements RecipeRepositoryInterface
{
    protected string $entityClass = Recipe::class;

    public function get(?int $id) : ?Recipe
    {
        return $this->getEntity($id);
    }

    public function getAllByItemId(int $itemId) : RecipeCollection
    {
        return RecipeCollection::from(
            $this->getAllByItemIdQuery($itemId)
        );
    }

    public function getByItemId(int $itemId) : ?Recipe
    {
        return $this
            ->getAllByItemIdQuery($itemId)
            ->one();
    }

    protected function getAllByItemIdQuery(int $itemId) : Query
    {
        return $this
            ->query()
            ->where('creates_id', $itemId)
            ->whereGt('creates_min', 0);
    }

    public function getFilteredCount(
        ?int $skillId,
        ?string $searchQuery
    ) : int
    {
        return $this
            ->getAllFilteredQuery($skillId, $searchQuery)
            ->count();
    }

    public function getFilteredPage(
        ?int $skillId,
        ?string $searchQuery,
        int $offset,
        int $pageSize
    ) : RecipeCollection
    {
        return RecipeCollection::from(
            $this
                ->getAllFilteredQuery($skillId, $searchQuery)
                ->slice($offset, $pageSize)
        );
    }

    protected function getAllFilteredQuery(
        ?int $skillId,
        ?string $searchQuery
    ) : Query
    {
        $query = $this->query();

        if ($skillId > 0) {
            $query = $query->where('skill_id', $skillId);
        }

        if (strlen($searchQuery) > 0) {
            $query = $query->search(
                $searchQuery,
                '(name like ? or name_ru like ?)',
                2
            );
        }
        
        return $query
            ->orderByAsc('learnedat')
            ->thenByAsc('lvl_orange')
            ->thenByAsc('lvl_yellow')
            ->thenByAsc('lvl_green')
            ->thenByAsc('lvl_gray')
            ->thenByAsc('name_ru');
    }

    public function getByName(string $name) : ?Recipe
    {
        return $this
            ->query()
            ->whereRaw('(name like ? or name_ru like ?)', [$name, $name])
            ->one();
    }
}
