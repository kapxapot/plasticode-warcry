<?php

namespace App\Models;

use Plasticode\Models\Model;
use Plasticode\Models\Interfaces\LinkableInterface;

class NewsYear extends Model implements LinkableInterface
{
    private int $year;
    private string $url;

    public function __construct(int $year, string $url)
    {
        parent::__construct();

        $this->year = $year;
        $this->url = $url;
    }

    public function url() : ?string
    {
        return $this->url;
    }

    public function title() : string
    {
        return $this->year . ' год';
    }
}
