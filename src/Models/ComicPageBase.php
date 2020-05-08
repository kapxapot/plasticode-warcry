<?php

namespace App\Models;

use App\Models\Traits\Stamps;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Published;

/**
 * @property integer $number
 */
abstract class ComicPageBase extends DbModel
{
    use Published;
    use Stamps;

    protected static string $sortField = 'number';

    protected static string $comicIdField;

    public static function comicIdField() : string
    {
        return static::$comicIdField;
    }

    /**
     * @return static
     */
    public static function createForComic(Comic $comic) : self
    {
        return static::create(
            [static::$comicIdField => $comic->getId()]
        );
    }

    abstract public function comic() : Comic;

    abstract public function pageUrl() : string;

    public function url() : string
    {
        return self::$container->linker->comicPageImg($this);
    }

    public function thumbUrl() : string
    {
        return self::$container->linker->comicThumbImg($this);
    }

    public function numberStr() : string
    {
        return str_pad($this->number, 2, '0', STR_PAD_LEFT);
    }
    
    public function ext() : string
    {
        return self::$container->linker->getImageExtension($this->picType);
    }
    
    private function getSiblings() : Query
    {
        return self::getPublished()
            ->where(static::$comicIdField, $this->{static::$comicIdField});
    }

    public function prev() : ?self
    {
        return $this->lazy(
            function () {
                $prev = $this->getSiblings()
                    ->whereLt('number', $this->number)
                    ->orderByDesc('number')
                    ->one();

                if (!$prev) {
                    $prevComic = $this->comic()->prev();
                    
                    if ($prevComic) {
                        $prev = $prevComic->last();
                    }
                }
                
                return $prev;
            }
        );
    }
    
    public function next() : ?self
    {
        return $this->lazy(
            function () {
                $next = $this->getSiblings()
                    ->whereGt('number', $this->number)
                    ->orderByAsc('number')
                    ->one();

                if (!$next) {
                    $nextComic = $this->comic()->next();
                    
                    if ($nextComic) {
                        $next = $nextComic->first();
                    }
                }
                
                return $next;
            }
        );
    }

    public function titleName() : string
    {
        return $this->numberStr() . ' - ' . $this->comic()->titleName();
    }
}
