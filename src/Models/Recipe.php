<?php

namespace App\Models;

use Plasticode\Models\DbModel;

/**
 * @property integer $id
 * @property integer $createsId
 * @property integer $createsMin
 * @property integer $createsMax
 * @property integer|null $lvlOrange
 * @property integer|null $lvlYellow
 * @property integer|null $lvlGreen
 * @property integer|null $lvlGray
 * @property string $name
 * @property string $nameRu
 * @property integer $skillId
 * @property string $source
 * @method Skill skill()
 * @method string[] sources() 
 * @method string url()
 * @method self withSkill(Skill|callable $skill)
 * @method self withSources(string[]|callable $sources)
 * @method self withUrl(string|callable $url)
 * @method array baseReagents()
 * @method array link()
 * @method array reagentsList()
 * @method array requiredSkills()
 * @method self withBaseReagents(array|callable $baseReagents)
 * @method self withLink(array|callable $link)
 * @method self withReagentsList(array|callable $reagentsList)
 * @method self withRequiredSkills(array|callable $requiredSkills)
 */
class Recipe extends DbModel
{
    protected bool $built = false;
    protected bool $forceBuild = false;

    protected function requiredWiths(): array
    {
        return ['url', 'sources', 'skill'];
    }

    public function title() : string
    {
        $title = $this->nameRu;

        if ($this->name && $this->name != $this->nameRu) {
            $title .= ' (' . $this->name . ')';
        }

        return $title;
    }

    public function levels() : array
    {
        return [
            'orange' => $this->lvlOrange,
            'yellow' => $this->lvlYellow,
            'green' => $this->lvlGreen,
            'gray' => $this->lvlGray,
        ];
    }

    public function invQuality() : int
    {
        return 8 - $this->quality;
    }

    public function reset() : void
    {
        $this->built = false;
        $this->forceBuild = true;
    }

    protected function buildIfNeeded() : void
    {
        if (!$this->built || $this->forceBuild) {
            $this->build($this->forceBuild);
        }
    }

    public function reagentsArray() : array
    {
        $reagents = [];

        if (strlen($this->reagents) > 0) {
            $chunks = explode(',', $this->reagents);

            foreach ($chunks as $chunk) {
                [$id, $count] = explode('x', $chunk);
                $reagents[$id] = $count;
            }
        }

        return $reagents;
    }
}
