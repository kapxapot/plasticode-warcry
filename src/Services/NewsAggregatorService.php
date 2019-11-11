<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Event;
use App\Models\ForumTopic;
use App\Models\News;
use App\Models\NewsYear;
use App\Models\Video;
use Plasticode\Collection;
use Plasticode\Util\Arrays;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;

class NewsAggregatorService
{
    private $sources = [
        Article::class,
        Event::class,
        ForumTopic::class,
        News::class,
        Video::class
    ];
    
    private $strictSources = [
        ForumTopic::class,
        News::class,
    ];
    
    private function getSources(bool $strict = false)
    {
        return $strict
            ? $this->strictSources
            : $this->sources;
    }
    
    private function withSources(bool $strict, callable $action) : array
    {
        return array_map($action, $this->getSources($strict));
    }
    
    private function collect(bool $strict, callable $action) : Collection
    {
        return Collection::merge(...$this->withSources($strict, $action));
    }
    
    /**
     * Sorts news in descending order by publish date.
     * Reverse sort = ascending order.
     */
    private function sort(Collection $collection, bool $reverse = false) : Collection
    {
        return $reverse
            ? $collection->asc('published_at', Sort::DATE)
            : $collection->desc('published_at', Sort::DATE);
    }
    
    private function sortReverse(Collection $collection) : Collection
    {
        return $this->sort($collection, true);
    }

    public function getByTag($tag, bool $strict = true) : Collection
    {
        $all = $this->collect($strict, function ($s) use ($tag) {
            return $s::getNewsByTag($tag)->all();
        });

        return $this->sort($all);
    }

    public function getCount($game = null, bool $strict = false) : int
    {
        $counts = $this->withSources($strict, function ($s) use ($game, $exceptNewsId) {
            return $s::getLatestNews($game, $exceptNewsId)->count();
        });
        
        return array_sum($counts);
    }
    
    public function getLatest($game, $limit, $exceptNewsId = null, bool $strict = true) : Collection
    {
        return $this->getPage($game, 1, $limit, $exceptNewsId, $strict);
    }
    
    private function dump($data)
    {
        var_dump($data->map(function ($item) {
            return [
                $item->entityAlias(), $item->id, $item->publishedAt
            ];
        }));
    }

    public function getPage($game = null, int $page = 1, int $pageSize = 7, $exceptNewsId = null, bool $strict = false) : Collection
    {
        if ($page < 1) {
            $page = 1;
        }
        
        if ($pageSize < 1) {
            throw new \InvalidArgumentException('$pageSize must be a positive integer.');
        }

        $offset = ($page - 1) * $pageSize;
        
        // optimization for faster load
        $loadLimit = $offset + $pageSize;

        $all = $this->collect($strict, function ($s) use ($game, $exceptNewsId, $loadLimit) {
            return $s::getLatestNews($game, $exceptNewsId)
                ->limit($loadLimit)
                ->all();
        });

        $all = $this->sort($all);

        return $all->slice($offset, $pageSize);
    }
    
    /**
     * Looks for News or ForumTopic with the provided id
     */
    public function getNews($newsId)
    {
        return News::findProtected($newsId) ?? ForumTopic::getNews($newsId);
    }
    
    public function getPrev($news, $strict = true)
    {
        $date = $news->publishedAt;
        $game = $news->game
            ? $news->game->root
            : null;

        $allPrev = $this->collect($strict, function ($s) use ($game, $date) {
            return $s::getNewsBefore($game, $date)
                ->limit(1)
                ->all();
        });
        
        $allPrev = $this->sort($allPrev);
        
        return $allPrev->first();
    }
    
    public function getNext($news, $strict = true)
    {
        $date = $news->publishedAt;
        $game = $news->game
            ? $news->game->root
            : null;
        
        $allNext = $this->collect($strict, function ($s) use ($game, $date) {
            return $s::getNewsAfter($game, $date)
                ->limit(1)
                ->all();
        });

        $allNext = $this->sortReverse($allNext);
        
        return $allNext->first();
    }
    
    private function getAllRaw(bool $strict = false) : Collection
    {
        return $this->collect($strict, function ($s) {
            return $s::getLatestNews()->all();
        });
    }
    
    public function getTop($limit, bool $strict = false) : Collection
    {
        return $this->getPage(null, 1, $limit, null, $strict);
    }

    /**
     * Descending.
     */
    public function getYears(bool $strict = true) : Collection
    {
        $byYear = self::getAllRaw($strict)
            ->group(function ($item) {
                return Date::year($item->publishedAtIso());
            });

        $years = array_keys($byYear);
        
        return Collection::make($years)
            ->map(function ($y) {
                return new NewsYear($y);
            })
            ->desc('year');
    }
    
    public function getPrevYear($year, bool $strict = true)
    {
        return $this->getYears($strict)
            ->where(function ($y) use ($year) {
                return $y->year < $year;
            })
            ->desc('year')
            ->first();
    }
    
    public function getNextYear($year, bool $strict = true)
    {
        return $this->getYears($strict)
            ->where(function ($y) use ($year) {
                return $y->year > $year;
            })
            ->asc('year')
            ->first();
    }
    
    public function getByYear($year, bool $strict = true) : array
    {
        $byYear = $this->collect($strict, function ($s) use ($year) {
            return $s::getNewsByYear($year)->all();
        });

        $sorted = $this->sort($byYear);
        
        $monthly = [];
        
        foreach ($sorted as $s) {
            $month = Date::month($s->publishedAtIso());
            
            if (!array_key_exists($month, $monthly)) {
                $monthly[$month] = [
                    'label' => Date::SHORT_MONTHS[$month],
                    'full_label' => Date::MONTHS[$month],
                    'news' => [],
                ];
            }
            
            $monthly[$month]['news'][] = $s;
        }

        ksort($monthly);

        return $monthly;
    }
}
