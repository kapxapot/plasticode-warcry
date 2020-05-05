<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\Linkable;

/**
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
 * @method array baseReagents()
 * @method array link()
 * @method array reagentsList()
 * @method array requiredSkills()
 * @method Skill skill()
 * @method string[] sources() 
 * @method static withBaseReagents(array|callable $baseReagents)
 * @method static withLink(array|callable $link)
 * @method static withReagentsList(array|callable $reagentsList)
 * @method static withRequiredSkills(array|callable $requiredSkills)
 * @method static withSkill(Skill|callable $skill)
 * @method static withSources(string[]|callable $sources)
 */
class Recipe extends DbModel implements LinkableInterface
{
    use Linkable;

    protected bool $built = false;
    protected bool $forceBuild = false;

    protected function requiredWiths(): array
    {
        return [
            $this->urlPropertyName,
            'sources',
            'skill',
        ];
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
