<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;

/**
 * @property string $name
 * @property string|null $plural
 */
class EventType extends DbModel
{
    use Stamps;
}
