<?php

namespace App\Config\Parsing;

use App\Parsing\TagMappers\BluepostMapper;
use Plasticode\Config\Parsing\BBContainerConfig as BBContainerConfigBase;

class BBContainerConfig extends BBContainerConfigBase
{
    public function __construct()
    {
        parent::__construct();

        $this->register('bluepost', new BluepostMapper(), 'quote');
    }
}
