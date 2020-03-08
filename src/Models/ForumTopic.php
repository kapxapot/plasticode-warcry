<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Arrays;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;

class ForumTopic extends DbModel implements NewsSourceInterface
{
    use Tags;

    const TimeFormat = '%Y-%m-%d %H:%M:%S';
    
    protected static $idField = 'tid';
    
    protected static $sortField = 'start_date';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getTagsEntityType() : string
    {
        return News::getTable();
    }
    
    public function getTags() : array
    {
        $tags = $this->tags()
            ->extract('tag_text')
            ->toArray();
        
        return Arrays::trim($tags);
    }

    public static function filterByTag(Query $query, string $tag) : Query
    {
        $ids = ForumTag::getForumTopicIdsByTag($tag);
        
        if ($ids->empty()) {
            return Query::empty();
        }

        return $query->whereIn('tid', $ids);
    }

    public static function getByTag(string $tag) : Query
    {
        return self::filterByTag(self::query(), $tag);
    }
    
    // queries
    
    private static function getNewsQuery(Game $game = null) : Query
    {
        $forumIds = Game::getNewsForumIds($game);

        if ($forumIds->empty()) {
            return Query::empty();
        }
        
        return self::query()
            ->whereIn('forum_id', $forumIds);
    }

    // getters - one
    
    public static function getNews(int $id) : ?self
    {
        $topic = self::get($id);
        
        if (!$topic || !$topic->isNews()) {
            return null;
        }
        
        return $topic;
    }

    // props
    
    public function isNews() : bool
    {
        return $this->forum()->isNewsForum();
    }
    
    public function game() : ?Game
    {
        return Game::getByForumId($this->forumId);
    }
    
    public function rootGame() : ?Game
    {
        return $this->game()
            ? $this->game()->root()
            : null;
    }
    
    public function forum() : Forum
    {
        return Forum::get($this->forumId);
    }

    public function forumUrl() : string
    {
        return self::$container->linker->forumTopic($this->getId());
    }

    public function forumPost() : ForumPost
    {
        return $this->lazy(
            function () {
                return ForumPost::getByForumTopic($this->getId());
            }
        );
    }
    
    public function post() : ?string
    {
        return $this->lazy(
            function () {
                return $this->forumPost()
                    ? $this->forumPost()->post
                    : null;
            }
        );
    }
    
    private function parsedPost() : ?string
    {
        return $this->lazy(
            function () {
                $post = $this->post();

                if (is_null($post)) {
                    return null;
                }

                $newsParser = self::$container->newsParser;
                $forumParser = self::$container->forumParser;
                
                $post = $newsParser->beforeParsePost($post, $this->getId());
                $post = $forumParser->convert(['TEXT' => $post, 'CODE' => 1]);
                $post = $newsParser->afterParsePost($post);
                
                return $post;
            }
        );
    }

    public function tags() : Collection
    {
        return ForumTag::getByForumTopic($this->getId())->all();
    }
    
    public function largeImage() : ?string
    {
        return null;
    }
    
    public function image() : ?string
    {
        return null;
    }

    public function video() : ?string
    {
        return null;
    }
    
    public function published() : int
    {
        return 1;
    }
    
    public function publishedAt() : string
    {
        return strftime(self::TimeFormat, $this->startDate);
    }

    public function publishedAtIso() : string
    {
        return Date::iso($this->publishedAt());
    }
    
    public function creator() : array
    {
        return [
            'forum_member' => ForumMember::get($this->starterId),
            'display_name' => $this->starterName,
        ];
    }
    
    public function updater() : array
    {
        return $this->creator();
    }
    
    public function createdAtIso() : string
    {
        return $this->publishedAtIso();
    }
    
    public function updatedAtIso() : string
    {
        return $this->createdAtIso();
    }

    // LinkableInterface
    
    public function url() : ?string
    {
        return self::$container->linker->news($this->getId());
    }

    // NewsSourceInterface
    
    public static function getNewsByTag(string $tag) : Query
    {
        return self::filterByTag(self::getNewsQuery(), $tag);
    }
    
    public static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query
    {
        $query = self::getNewsQuery($game);

        if ($exceptNewsId) {
            $query = $query->whereNotEqual(static::$idField, $exceptNewsId);
        }

        return $query;
    }
    
    public static function getNewsBefore(Game $game, string $date) : Query
    {
        $convertedDate = strtotime($date);
        
        return self::getNewsQuery($game)
            ->whereLt('start_date', $convertedDate)
            ->orderByDesc('start_date');
    }
    
    public static function getNewsAfter(Game $game, string $date) : Query
    {
        $convertedDate = strtotime($date);
        
        return self::getNewsQuery($game)
            ->whereGt('start_date', $convertedDate)
            ->orderByAsc('start_date');
    }

    public static function getNewsByYear(int $year) : Query
    {
        return self::getNewsQuery()
            ->whereRaw('(year(from_unixtime(start_date)) = ?)', [$year]);
    }
    
    public function displayTitle() : string
    {
        return self::$container->newsParser->decodeTopicTitle($this->title);
    }
    
    public function fullText() : ?string
    {
        return $this->lazy(
            function () {
                $cutParser = self::$container->cutParser;
                $text = $this->parsedPost();
                
                return $cutParser->full($text);
            }
        );
    }
    
    public function shortText() : ?string
    {
        return $this->lazy(
            function () {
                $cutParser = self::$container->cutParser;
                $text = $this->parsedPost();
                
                return $cutParser->short($text);
            }
        );
    }
}
