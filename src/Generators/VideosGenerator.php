<?php

namespace App\Generators;

use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Traits\Publishable;

class VideosGenerator extends TaggableEntityGenerator
{
    use Publishable;

    public function beforeSave(array $data, $id = null) : array
    {
        $data = parent::beforeSave($data, $id);

        $data = $this->publishIfNeeded($data);

        return $data;
    }
}
