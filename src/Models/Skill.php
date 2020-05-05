<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property integer $active
 * @property string|null $alias
 * @property string|null $icon
 * @property string $name
 * @property string $nameRu
 * @method string defaultIcon()
 * @method string iconUrl()
 * @method static withDefaultIcon(string|callable $defaultIcon)
 * @method static withIconUrl(string|callable $iconUrl)
 */
class Skill extends DbModel
{
    protected function requiredWiths(): array
    {
        return ['defaultIcon', 'iconUrl'];
    }

    public function displayIcon()
    {
        return $this->icon ?? $this->defaultIcon();
    }
}
