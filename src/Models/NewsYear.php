<?php

namespace App\Models;

use Plasticode\Models\Model;
use Plasticode\Models\Interfaces\LinkableInterface;

class NewsYear extends Model implements LinkableInterface
{
    /**
     * Year
     *
     * @var integer
     */
    private $year;

    public function __construct(int $year)
    {
        parent::__construct();
        
        $this->year = $year;
    }
    
    public function url() : ?string
    {
        return self::$container->linker->newsYear($this->year);
    }
    
    public function title() : string
    {
        return $this->year . ' год';
    }
}
