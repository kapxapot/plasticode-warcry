<?php

namespace App\ViewModels;

use Plasticode\ViewModels\QuoteViewModel;

class BluepostViewModel extends QuoteViewModel
{
    public function author() : ?string
    {
        return parent::author() ?? 'Blizzard';
    }

    public function style() : ?string
    {
        return 'quote--bluepost';
    }
}
