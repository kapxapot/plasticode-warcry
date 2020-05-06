<?php

namespace App\Models;

use App\Collections\ComicIssueCollection;
use App\Models\Traits\Names;
use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Tagged;

/**
 * @property string $alias
 * @property string|null $description
 * @property integer $gameId
 * @property string $nameEn
 * @property string|null $nameRu
 * @property integer $publisherId
 * @property string|null $tags
 * @method Game game()
 * @method ComicIssueCollection issues()
 * @method string pageUrl()
 * @method ComicPublisher publisher()
 * @method static withGame(Game|callable $game)
 * @method static withIssues(ComicIssueCollection $issues)
 * @method static withPageUrl(string|callable $pageUrl)
 * @method static withPublisher(ComicPublisher|callable $publisher)
 */
class ComicSeries extends DbModel
{
    use FullPublished;
    use Names;
    use Stamps;
    use Tagged;

    protected function requiredWiths(): array
    {
        return [
            'game',
            'issues',
            'pageUrl',
            'publisher',
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
