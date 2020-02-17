<?php

namespace App\Config\Parsing;

use Plasticode\Config\Parsing\BBContainerConfig as BBContainerConfigBase;
use App\Parsing\Mappers\BluepostMapper;

class BBContainerConfig extends BBContainerConfigBase
{
    public function __construct()
    {
        parent::__construct();

        $this->register('bluepost', new BluepostMapper(), 'quote');
    }
}
