<?php

namespace App\Parsing\TagMappers;

use App\ViewModels\BluepostViewModel;
use Plasticode\Parsing\TagMappers\QuoteMapper;

class BluepostMapper extends QuoteMapper
{
    protected static $viewModelClass = BluepostViewModel::class;
}
