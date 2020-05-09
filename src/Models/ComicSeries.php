<?php

namespace App\Models;

use App\Collections\ComicIssueCollection;
use App\Models\Traits\ComicCommon;
use App\Models\Traits\ComicRoot;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\TaggedInterface;

/**
 * @method ComicIssueCollection issues()
 * @method static withIssues(ComicIssueCollection $issues)
 */
class ComicSeries extends DbModel implements TaggedInterface
{
    use ComicCommon;
    use ComicRoot;

    protected function requiredWiths(): array
    {
        return [
            ...$this->comicCommonProperties(),
            ...$this->comicRootProperties(),
            'issues',
        ];
    }

    public function issueByNumber(int $number) : ?ComicIssue
    {
        return $this->issues()->byNumber($number);
    }

    public function prevIssue(int $number) : ?ComicIssue
    {
        return $this->issues()->prev($number);
    }

    public function nextIssue(int $number) : ?ComicIssue
    {
        return $this->issues()->next($number);
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

    public function maxIssueNumber() : int
    {
        return $this->issues()->maxNumber();
    }
}
