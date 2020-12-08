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

    protected function requiredWiths() : array
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

    public function count() : int
    {
        return $this->issues()->count();
    }

    public function cover() : ?ComicPage
    {
        return $this->firstIssue()
            ? $this->firstIssue()->cover()
            : null;
    }

    public function firstIssue() : ?ComicIssue
    {
        return $this->issues()->first();
    }

    public function lastIssue() : ?ComicIssue
    {
        return $this->issues()->last();
    }

    public function prevIssue(int $number) : ?ComicIssue
    {
        return $this->issues()->prevBy($number);
    }

    public function nextIssue(int $number) : ?ComicIssue
    {
        return $this->issues()->nextBy($number);
    }

    public function lastIssuedOn() : ?string
    {
        return $this->lastIssue()
            ? $this->lastIssue()->issuedOn
            : null;
    }

    public function maxIssueNumber() : int
    {
        return $this->issues()->maxNumber();
    }
}
