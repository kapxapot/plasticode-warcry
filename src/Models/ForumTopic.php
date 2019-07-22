<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;

use App\Models\Interfaces\NewsSourceInterface;

class ForumTopic extends DbModel implements NewsSourceInterface
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
    
    protected function getTags() : array
    {
        $tags = $this->tags()
            ->extract('tag_text')
            ->toArray();
        
        return Strings::trimArray($tags);
    }

    public static function filterByTag($query, $tag) : Query
    {
        $ids = ForumTag::getForumTopicIdsByTag($tag);
        
        if ($ids->empty()) {
            return Query::empty();
        }

        return $query->whereIn('tid', $ids);
    }

    public static function getByTag($tag) : Query
    {
        return self::filterByTag(self::query(), $tag);
    }
    
    // queries
    
    private static function getNewsQuery($game = null) : Query
    {
        $forumIds = Game::getNewsForumIds($game);

        if ($forumIds->empty()) {
            return Query::empty();
        }
        
        return self::query()
            ->whereIn('forum_id', $forumIds);
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

    public function forumUrl()
    {
        return self::$linker->forumTopic($this->getId());
    }

    public function forumPost()
    {
        return $this->lazy(function () {
            return ForumPost::getByForumTopic($this->getId());
        });
    }
    
    public function post()
    {
        return $this->lazy(function () {
            return $this->forumPost()
                ? $this->forumPost()->post
                : null;
        });
    }
    
    private function parsedPost()
    {
        return $this->lazy(function () {
            $newsParser = self::$container->newsParser;
            $forumParser = self::$container->forumParser;
            
            $post = $newsParser->beforeParsePost($this->post(), $this->getId());
            $post = $forumParser->convert([ 'TEXT' => $post, 'CODE' => 1 ]);
            $post = $newsParser->afterParsePost($post);
            
            return $post;
        });
    }

    public function tags()
    {
        return ForumTag::getByForumTopic($this->getId())->all();
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

    // LinkableInterface
    
    public function url()
    {
        return self::$linker->news($this->getId());
    }

    // NewsSourceInterface
    
    public static function getNewsByTag($tag) : Query
    {
        return self::filterByTag(self::getNewsQuery(), $tag);
    }
    
    public static function getLatestNews($game = null, $exceptNewsId = null) : Query
    {
        $query = self::getNewsQuery($game);

        if ($exceptNewsId) {
            $query = $query->whereNotEqual(static::$idField, $exceptNewsId);
        }

        return $query;
    }
    
    public static function getNewsBefore($game, $date) : Query
    {
        $convertedDate = strtotime($date);
        
        return self::getNewsQuery($game)
            ->whereLt('start_date', $convertedDate)
            ->orderByDesc('start_date');
    }
    
    public static function getNewsAfter($game, $date) : Query
    {
        $convertedDate = strtotime($date);
        
        return self::getNewsQuery($game)
            ->whereGt('start_date', $convertedDate)
            ->orderByAsc('start_date');
    }

    public static function getNewsByYear($year) : Query
    {
        return self::getNewsQuery()
            ->whereRaw('(year(from_unixtime(start_date)) = ?)', [ $year ]);
    }
    
    public function displayTitle()
    {
        return self::$container->newsParser->decodeTopicTitle($this->title);
    }
    
    public function fullText()
    {
        return $this->lazy(function () {
            return self::$parser->parseCut($this->parsedPost());
        });
    }
    
    public function shortText()
    {
        return $this->lazy(function () {
            return self::$parser->parseCut($this->parsedPost(), $this->url(), false);
        });
    }
}
