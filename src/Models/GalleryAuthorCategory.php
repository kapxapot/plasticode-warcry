<?php

namespace App\Models;

use App\Collections\GalleryAuthorCollection;
use Plasticode\Models\DbModel;

/**
 * @property string $alias
 * @property string $name
 * @property integer $position
 * @method GalleryAuthorCollection authors()
 * @method static withAuthors(GalleryAuthorCollection|callable $authors)
 */
class GalleryAuthorCategory extends DbModel
{
    protected function requiredWiths() : array
    {
        return ['authors'];
    }
}
