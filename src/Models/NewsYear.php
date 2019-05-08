<?php

namespace App\Models;

use Plasticode\Models\Model;
use Plasticode\Models\Interfaces\LinkableInterface;

class NewsYear extends Model implements LinkableInterface
{
    public function __construct(int $year)
    {
        parent::__construct();
        
        $this->year = $year;
    }
    
    public function url()
    {
        return self::$linker->newsYear($this->year);
    }
    
    public function title()
    {
        return $this->year . ' год';
    }
}
