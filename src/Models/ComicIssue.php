<?php

namespace App\Models;

use App\Collections\ComicIssuePageCollection;
use App\Models\Interfaces\NumberedInterface;
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
 * @method static withPages(ComicIssuePageCollection|callable $pages)
 */
class ComicIssue extends Comic implements NumberedInterface
{
    use ComicCommon;

    protected function requiredWiths() : array
    {
        return [
            ...parent::requiredWiths(),
            'series',
        ];
    }

    public function pages() : ComicIssuePageCollection
    {
        return ComicIssuePageCollection::from(
            parent::pages()
        );
    }

    public function number() : int
    {
        // Well this is ugly, but it works.
        // Todo: remove \ORM from DbModel, make models pure.
        return $this->getObj()['number'];
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
