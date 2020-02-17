<?php

namespace App\Parsing\Mappers;

use App\ViewModels\BluepostViewModel;
use Plasticode\Parsing\Mappers\QuoteMapper;

class BluepostMapper extends QuoteMapper
{
    protected static $viewModelClass = BluepostViewModel::class;
}
