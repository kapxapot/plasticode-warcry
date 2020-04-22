<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property string $name
 * @property string $nameRu
 * @property string|null $icon
 * @property integer $active
 * @property string|null $alias
 * @method string defaultIcon()
 * @method string iconUrl()
 * @method self withDefaultIcon(string|callable $defaultIcon)
 * @method self withIconUrl(string|callable $iconUrl)
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
