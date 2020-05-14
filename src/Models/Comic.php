<?php

namespace App\Models;

use App\Collections\ComicPageCollection;
use App\Models\Traits\ComicCommon;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\TaggedInterface;

/**
 * @property string $issuedOn
 * @property string|null $origin
 */
abstract class Comic extends DbModel implements TaggedInterface
{
    use ComicCommon;

    protected string $pagesPropertyName = 'pages';

    protected function requiredWiths(): array
    {
        return [
            ...$this->comicCommonProperties(),
            $this->pagesPropertyName,
        ];
    }

    public function pages() : ComicPageCollection
    {
        return $this->getWithProperty(
            $this->pagesPropertyName
        );
    }

    /**
     * @return static|null
     */
    abstract public function prev() : ?self;

    /**
     * @return static|null
     */
    abstract public function next() : ?self;

    public function pageByNumber(int $number) : ?ComicPage
    {
        return $this->pages()->byNumber($number);
    }

    public function count() : int
    {
        return $this->pages()->count();
    }

    public function cover() : ?ComicPage
    {
        return $this->firstPage();
    }

    public function firstPage() : ?ComicPage
    {
        return $this->pages()->first();
    }

    public function lastPage() : ?ComicPage
    {
        return $this->pages()->last();
    }

    public function prevPage(int $number) : ?ComicPage
    {
        $prev = $this->pages()->prevBy($number);

        if (!$prev && $this->prev()) {
            $prev = $this->prev()->lastPage();
        }

        return $prev;
    }

    public function nextPage(int $number) : ?ComicPage
    {
        $next = $this->pages()->nextBy($number);

        if (!$next && $this->next()) {
            $next = $this->next()->firstPage();
        }

        return $next;
    }

    public function maxPageNumber() : int
    {
        return $this->pages()->maxNumber();
    }

    abstract function titleName() : string;
}
