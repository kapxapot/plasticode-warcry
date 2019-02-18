<?php

namespace App\Services;

use Plasticode\Collection;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;

use App\Models\ForumTopic;
use App\Models\News;

class NewsAggregatorService
{
    private function sort($collection)
    {
        return $collection->desc('published_at', Sort::DATE);
    }
    
    public function getByTag($tag)
    {
        $all = Collection::merge(
            News::getByTag($tag),
            ForumTopic::getNewsByTag($tag)
        );

		return $this->sort($all);
    }
    
    public function getByGame($game, $exceptNewsId = null)
    {
        $news = News::getByGame($game, $exceptNewsId);
        $forumNews = ForumTopic::getNewsByGame($game, $exceptNewsId);

        $byGame = Collection::merge($news, $forumNews);

        return $this->sort($byGame);
    }
    
    public function count($game, $exceptNewsId = null)
    {
        return News::count($game, $exceptNewsId) + ForumTopic::newsCount($game, $exceptNewsId);
    }
    
	public function getLatest($game, $limit, $exceptNewsId = null)
	{
		return $this->getPage($game, 1, $limit, $exceptNewsId);
	}

	public function getPage($game = null, $page = 1, $pageSize = 7, $exceptNewsId = null)
	{
		$offset = ($page - 1) * $pageSize;
		$goal = $pageSize;

		$news = News::getLatest($game, $offset, $goal, $exceptNewsId);
		$newsCount = $news->count();
		
		$goal -= $newsCount;
		
		$all = $news;
		
		if ($goal > 0) {
			if ($newsCount > 0) {
				$offset = 0;
			}
			else {
				$offset -= News::getByGame($game, $exceptNewsId)->count();
			}

			$forumNews = ForumTopic::getLatestNews($game, $offset, $goal, $exceptNewsId);
			$all = $all->concat($forumNews);
		}

		return $this->sort($all);
	}
	
	private function getAllRaw()
	{
	    return Collection::merge(
	        News::getAllPublished(),
	        ForumTopic::getAllNews()
        );
	}
	
	public function getTop($limit)
	{
	    return $this->getPage($game, 1, $limit);
	}
	
	public function getAll()
	{
	    $all = $this->getAllRaw();

        return $this->sort($all);
	}

	public function getYears()
	{
	    $byYear = self::getAllRaw()
	        ->group(function ($item) {
	            return Date::year($item->publishedAtIso());
	        });

		$years = array_keys($byYear);
		rsort($years);
		
		return $years;
	}
	
	public function getByYear($year)
	{
	    $byYear = Collection::merge(
	        ForumTopic::getNewsByYear($year),
	        News::getByYear($year)
        );

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
