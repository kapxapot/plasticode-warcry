<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;

/**
 * @property string $name
 * @property string|null $website
 */
class ComicPublisher extends DbModel
{
    use Stamps;
}
