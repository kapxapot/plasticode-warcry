<?php

namespace App\Models;

use App\Collections\RecipeCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer|null $avgbuyout
 * @property integer|null $buyprice
 * @property string $icon
 * @property string $name
 * @property string|null $nameRu
 * @property integer|null $quality
 * @property integer|null $sellprice
 * @method RecipeCollection recipes()
 * @method static withRecipes(RecipeCollection|callable $recipes)
 */
class Item extends DbModel implements LinkableInterface
{
    use CreatedAt;
    use UpdatedAt;
    use Linkable;

    protected function requiredWiths(): array
    {
        return [
            $this->urlPropertyName,
            'recipes',
        ];
    }

    public function displayName() : string
    {
        return $this->nameRu ?? $this->name;
    }

    public function isFullyLoaded() : bool
    {
        return strlen($this->nameRu) > 0;
    }
}
