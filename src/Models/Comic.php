<?php

namespace App\Models;

use App\Collections\ComicPageBaseCollection;
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
            $this->pagesPropertyName,
            $this->pageUrlPropertyName,
            $this->parsedDescriptionPropertyName,
            $this->tagLinksPropertyName,
        ];
    }

    public function pages() : ComicPageBaseCollection
    {
        return $this->getWithProperty(
            $this->pagesPropertyName
        );
    }

    abstract public function createPage() : ComicPageBase;

    /**
     * @return static|null
     */
    abstract public function prev() : ?self;

    /**
     * @return static|null
     */
    abstract public function next() : ?self;

    public function pageByNumber(int $number) : ?ComicPageBase
    {
        return $this->pages()->byNumber($number);
    }

    public function count() : int
    {
        return $this->pages()->count();
    }

    public function cover()
    {
        return $this->first();
    }

    public function first() : ComicPageBase
    {
        return $this->pages()->first();
    }

    public function last() : ComicPageBase
    {
        return $this->pages()->last();
    }

    public function maxPageNumber() : int
    {
        return $this->pages()->maxNumber();
    }

    abstract function titleName() : string;
}
