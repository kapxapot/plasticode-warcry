<?php

namespace App\Models;

use App\Models\Traits\Names;
use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tagged;

class ComicSeries extends DbModel
{
    use Description;
    use FullPublished;
    use Names;
    use Stamps;
    use Tagged;

    protected static $tagsEntityType = 'comics';

    // getters - one

    public static function getPublishedByAlias(string $alias) : ?self
    {
        return self::getPublished()
            ->where('alias', $alias)
            ->one();
    }

    // GETTERS - MANY
    
    public static function getAllSorted() : Collection
    {
        $sorts = [
            'last_issued_on' => [ 'dir' => 'desc', 'type' => 'string' ],
        ];
        
        return self::getAll()->multiSort($sorts);
    }
    
    // PROPS
    
    public function game() : Game
    {
        return Game::get($this->gameId);
    }

    public function pageUrl() : string
    {
        return self::$container->linker->comicSeries($this);
    }
    
    public function issues() : Collection
    {
        return $this->lazy(
            function () {
                return ComicIssue::getBySeries($this->id)
                    ->all();
            }
        );
    }
    
    public function issueByNumber($number) : ?ComicIssue
    {
        return $this->issues()->where('number', $number)->first();
    }
    
    public function count() : int
    {
        return $this->issues()->count();
    }
    
    public function countStr() : string
    {
        return self::$container->cases->caseForNumber('выпуск', $this->count());
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
    
    public function lastIssuedOn()
    {
        return $this->last()
            ? $this->last()->issuedOn
            : null;
    }
    
    public function publisher() : ComicPublisher
    {
        return ComicPublisher::get($this->publisherId);
    }
    
    public function maxIssueNumber($exceptId = null)
    {
        $max = $this->issues()
            ->where(
                function ($issue) use ($exceptId) {
                    return $issue->id != $exceptId;
                }
            )
            ->asc('number')
            ->last();
        
        return $max ? $max->number : 0;
    }
}
