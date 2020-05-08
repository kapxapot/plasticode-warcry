<?php

namespace App\Models;

use App\Collections\ComicPageBaseCollection;
use App\Models\Traits\Description;
use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

abstract class Comic extends DbModel
{
    use Description;
    use FullPublished;
    use Stamps;
    use Tagged;

    abstract public function pages() : ComicPageBaseCollection;

    abstract public function createPage() : ComicPageBase;

    public function prev() : ?self
    {
        return null;
    }
    
    public function next() : ?self
    {
        return null;
    }

    public function pageByNumber($number)
    {
        return $this->pages()->where('number', $number)->first();
    }
    
    public function count() : int
    {
        return $this->pages()->count();
    }
    
    public function first()
    {
        return $this->pages()->first();
    }
    
    public function last()
    {
        return $this->pages()->last();
    }
    
    public function cover()
    {
        return $this->first();
    }

    public function maxPageNumber($exceptId = null) : int
    {
        $max = $this->pages(true)
            ->where(
                function ($page) use ($exceptId) {
                    return $page->id != $exceptId;
                }
            )
            ->asc('number')
            ->last();
        
        return $max ? $max->number : 0;
    }
}
