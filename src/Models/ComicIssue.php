<?php

namespace App\Models;

use App\Collections\ComicPageCollection;
use App\Models\Traits\ComicCommon;

/**
 * @property string $issuedOn
 * @property string|null $nameEn
 * @property string|null $nameRu
 * @property integer $number
 * @property string|null $origin
 * @property integer $seriesId
 * @method ComicSeries series()
 * @method static withSeries(ComicSeries|callable $series)
 * @method static withPages(ComicPageCollection|callable $pages)
 */
class ComicIssue extends Comic
{
    use ComicCommon;

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            'series',
        ];
    }

    public function createPage() : ComicPage
    {
        return ComicPage::createForComic($this);
    }

    public function pages() : ComicPageCollection
    {
        return ComicPageCollection::from(
            parent::pages()
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

    /**
     * @return static|null
     */
    public function prev() : ?self
    {
        return $this->series()->prevIssue($this->number);
    }

    /**
     * @return static|null
     */
    public function next() : ?self
    {
        return $this->series()->nextIssue($this->number);
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
