<?php

namespace App\Models;

use Plasticode\Models\Interfaces\LinkableInterface;

class NewsYear implements LinkableInterface
{
    private int $year;
    private string $url;

    public function __construct(int $year, string $url)
    {
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
