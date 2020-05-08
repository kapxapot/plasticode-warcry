<?php

namespace App\Models;

use Plasticode\Collections\Basic\Collection;

/**
 * @property string|null $nameEn
 * @property string|null $nameRu
 * @property integer $number
 * @property integer $seriesId
 */
class ComicIssue extends Comic
{
    protected static string $sortField = 'number';

    public function createPage() : ComicPage
    {
        return ComicPage::createForComic($this->getId());
    }
    
    public function series() : ComicSeries
    {
        return ComicSeries::get($this->seriesId);
    }
    
    public function pages(bool $ignoreCache = false) : Collection
    {
        return $this->lazy(
            function () {
                return ComicPage::getByComic($this->id)
                    ->all();
            },
            null,
            $ignoreCache
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
        return self::$container->linker->comicIssue($this);
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
