<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Parented;

/**
 * @property string|null $nameEn
 * @property string $nameRu
 * @property integer|null $parentId
 * @property integer $terminal
 */
class Region extends DbModel
{
    use Parented;
    use Stamps;

    protected function requiredWiths(): array
    {
        return [
            $this->parentPropertyName,
        ];
    }

    public function displayName()
    {
        $ru = [$this->nameRu];
        $en = [$this->nameEn];

        if ($this->hasParent() && !$this->isTerminal()) {
            $ru[] = $this->parent()->nameRu;
            $en[] = $this->parent()->nameEn;
        }

        $ruStr = implode(', ', array_filter($ru, 'strlen'));
        $enStr = implode(', ', array_filter($en, 'strlen'));

        return $ruStr . ($enStr ? ' (' . $enStr . ')' : '');
    }

    public function isTerminal() : bool
    {
        return self::toBool($this->terminal);
    }
}
