<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Stamps;

/**
 * @property string $name
 * @property string|null $plural
 */
class EventType extends DbModel
{
    use Stamps;
}
