<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Date;

class ForumTopic extends DbModel
{
    use Tags;
    
    protected static $idField = 'tid';
    
    protected static $sortField = 'start_date';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getTagsEntityType()
    {
        return News::getTable();
    }
    
    protected function getTags()
    {
        $tags = $this->tags();
        
        return array_map(function ($t) {
           return trim($t['tag_text']); 
        }, $tags->toArray());
    }
    
    // getters - many

	private static function getNewsQuery(Collection $forumIds, $offset = 0, $limit = 0, $exceptId = null, $year = null) {
        return function ($query) use ($forumIds, $offset, $limit, $exceptId, $year) {
    		$query = $query->whereIn('forum_id', $forumIds->toArray());
    
    		if ($exceptId) {
    			$query = $query->whereNotEqual(static::$idField, $exceptId);
    		}
    		
    		$query = $query->orderByDesc('start_date');
    			
    		if ($offset > 0 || $limit > 0) {
    			$query = $query
    				->offset($offset)
    				->limit($limit);
    		}
    		
    		if ($year > 0) {
    			$query = $query->whereRaw('(year(from_unixtime(start_date)) = ?)', [ $year ]);
    		}
            
            return $query;
        };
	}
    
	public static function getLatestNews($game = null, $offset = 0, $limit = 0, $exceptId = null, $year = null) {
		$forumIds = Game::getNewsForumIds($game);

        if ($forumIds->empty()) {
            return Collection::makeEmpty();
        }

        $query = self::getNewsQuery($forumIds, $offset, $limit, $exceptId, $year);
        return self::getMany($query);
	}
	
	public static function getNewsByYear($year)
	{
		return self::getLatestNews(null, 0, 0, null, $year);
	}
	
	public static function getNewsByGame($game, $exceptId = null)
	{
	    return self::getLatestNews($game, 0, 0, $exceptId);
	}
	
	public static function newsCount($game, $exceptId = null, $year = null)
	{
		$forumIds = Game::getNewsForumIds($game);

        if ($forumIds->empty()) {
            return Collection::makeEmpty();
        }

        $query = self::getNewsQuery($forumIds, $offset, $limit, $exceptId, $year);
        return self::getCount($query);
	}
	
	public static function getNewsByTag($tag)
	{
		$ids = ForumTag::getForumTopicIdsByTag($tag);
		
		if ($ids->empty()) {
			return Collection::makeEmpty();
		}
		
		$forumIds = Game::getNewsForumIds();
		
        if ($forumIds->empty()) {
            throw new \Exception('Empty forum ids list.');
        }

        return self::getMany(function ($q) use ($ids, $forumIds) {
		    return $q
			    ->whereIn('forum_id', $forumIds->toArray())
			    ->whereIn('tid', $ids->toArray());
        });
	}
	
	public static function getAllNews()
	{
	    return self::getLatestNews();
	}
	
	// getters - one
	
	public static function getNews($id)
	{
	    $topic = self::get($id);
	    
	    if (!$topic || !$topic->isNews()) {
	        return null;
	    }
	    
	    return $topic;
	}

    // props
    
    public function isNews()
    {
        return $this->forum()->isNewsForum();
    }
    
    public function game()
    {
        return Game::getByForumId($this->forumId);
    }
    
    public function forum()
    {
        return Forum::get($this->forumId);
    }

    public function url()
    {
        return self::$linker->news($this->getId());
    }
    
    public function forumUrl()
    {
        return self::$linker->forumTopic($this->getId());
    }

    public function forumPost()
    {
        return $this->lazy(__FUNCTION__, function () {
            return ForumPost::getByForumTopic($this->getId());
        });
    }
    
    public function post()
    {
        return $this->lazy(__FUNCTION__, function () {
            return $this->forumPost()
                ? $this->forumPost()->post
                : null;
        });
    }
    
    private function parsedPost()
    {
        return $this->lazy(__FUNCTION__, function () {
    	    $newsParser = self::$container->newsParser;
    	    $forumParser = self::$container->forumParser;
    	    
    		$post = $newsParser->beforeParsePost($this->post(), $this->getId());
    		$post = $forumParser->convert([ 'TEXT' => $post, 'CODE' => 1 ]);
    		$post = $newsParser->afterParsePost($post);
    		
    		return $post;
        });
    }
    
    public function fullText()
    {
        return $this->lazy(__FUNCTION__, function () {
            return self::$parser->parseCut($this->parsedPost());
        });
    }
    
    public function shortText()
    {
        return $this->lazy(__FUNCTION__, function () {
            return self::$parser->parseCut($this->parsedPost(), $this->url(), false);
        });
    }
    
    public function tags()
    {
        return ForumTag::getByForumTopic($this->getId());
    }
    
    public function largeImage()
    {
        return null;
    }
    
    public function image()
    {
        return null;
    }
    
    public function published()
    {
        return 1;
    }
    
    public function publishedAt()
    {
        return strftime(self::getSettings('time_format'), $this->startDate);
    }

    public function publishedAtIso()
    {
        return Date::iso($this->publishedAt());
    }
    
    public function creator()
    {
        return [
		    'forum_member' => ForumMember::get($this->starterId),
		    'display_name' => $this->starterName,
		];
    }
    
    public function updater()
    {
        return $this->creator();
    }
    
    public function createdAtIso()
    {
        return $this->publishedAtIso();
    }
    
    public function updatedAtIso()
    {
        return $this->createdAtIso();
    }
    
    public function displayTitle()
    {
        return self::$container->newsParser->decodeTopicTitle($this->title);
    }
}
