<?php

namespace App\Testing\Seeders;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Skill;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class SkillSeeder implements ArraySeederInterface
{
    private LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
    {
        $this->linker = $linker;
    }

    /**
     * @return Skill[]
     */
    public function seed() : array
    {
        $skill = new Skill(
            [
                'id' => 1,
                'name' => 'Some skill',
                'name_ru' => 'Какой-то навык',
            ]
        );

        $skill = $skill->withDefaultIcon('default_icon');
        $skill = $skill->withIconUrl(
            $this->linker->wowheadIcon($skill->defaultIcon())
        );

        return [$skill];
    }
}
