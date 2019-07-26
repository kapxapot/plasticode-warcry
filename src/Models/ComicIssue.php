<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;

class ComicIssue extends Comic
{
    protected static $sortField = 'number';

    // queries

    public static function getBySeries($seriesId) : Query
    {
        return self::getPublished()
            ->where('series_id', $seriesId);
    }
    
    // funcs
    
    public function createPage() : ComicPage
    {
        return ComicPage::createForComic($this->getId());
    }
    
    // PROPS
    
    public function series() : ComicSeries
    {
        return ComicSeries::get($this->seriesId);
    }
    
    public function pages() : Collection
    {
        return $this->lazy(
            function () {
                return ComicPage::getByComic($this->id)
                    ->all();
            }
        );
    }

    public function numberStr() : string
    {
        $numStr = '#' . $this->number;
        
        if ($this->nameRu) {
            $numStr .= ': ' . $this->nameRu;
        }

        return $numStr;
    }
    
    public function pageUrl() : string
    {
        return self::$linker->comicIssue($this);
    }

    public function prev() : ?Comic
    {
        return $this->lazy(
            function () {
                return self::getBySeries($this->seriesId)
                    ->whereLt('number', $this->number)
                    ->orderByDesc('number')
                    ->one();
            }
        );
    }
    
    public function next() : ?Comic
    {
        return $this->lazy(
            function () {
                return self::getBySeries($this->seriesId)
                    ->whereGt('number', $this->number)
                    ->orderByAsc('number')
                    ->one();
            }
        );
    }
    
    public function titleName() : string
    {
        $name = $this->series()->name() . ' ' . $this->numberStr();
        
        if ($this->series()->subName()) {
            $name .= ' (' . $this->series()->subName() . ')';
        }
        
        return $name;
    }
}
