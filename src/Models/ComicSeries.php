<?php

namespace App\Models;

use App\Collections\ComicIssueCollection;
use App\Models\Traits\ComicRoot;
use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @method ComicIssueCollection issues()
 * @method static withIssues(ComicIssueCollection $issues)
 */
class ComicSeries extends DbModel
{
    use ComicRoot;
    use FullPublished;
    use Stamps;
    use Tagged;

    protected function requiredWiths(): array
    {
        return [
            $this->gamePropertyName,
            $this->pageUrlPropertyName,
            $this->publisherPropertyName,
            'issues',
        ];
    }

    public function issueByNumber(int $number) : ?ComicIssue
    {
        return $this
            ->issues()
            ->first('number', $number);
    }

    public function count() : int
    {
        return $this->issues()->count();
    }

    public function first() : ?ComicIssue
    {
        return $this->issues()->first();
    }

    public function last() : ?ComicIssue
    {
        return $this->issues()->last();
    }

    public function cover() : ?ComicPageBase
    {
        return $this->first()
            ? $this->first()->cover()
            : null;
    }

    public function lastIssuedOn() : ?string
    {
        return $this->last()
            ? $this->last()->issuedOn
            : null;
    }

    public function maxIssueNumber(int $exceptId = 0) : int
    {
        $max = $this
            ->issues()
            ->where(
                fn (ComicIssue $i) => $i->getId() != $exceptId
            )
            ->asc('number')
            ->last();

        return $max ? $max->number : 0;
    }
}
