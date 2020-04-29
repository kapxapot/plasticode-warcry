<?php

namespace App\Repositories;

use App\Models\ForumTopic;
use App\Repositories\Interfaces\NewsSourceRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;

class ForumTopicRepository extends TaggedRepository implements NewsSourceRepositoryInterface
{
    protected string $entityClass = ForumTopic::class;

    protected string $sortField = 'start_date';
    protected bool $sortReverse = true;

    public function filterByTag(Query $query, string $tag) : Query
    {
        $ids = ForumTag::getForumTopicIdsByTag($tag);

        return $query->whereIn('tid', $ids);
    }

    public function getByTag(string $tag) : Query
    {
        return self::filterByTag(self::query(), $tag);
    }

    private static function getNewsQuery(Game $game = null) : Query
    {
        $forumIds = Game::getNewsForumIds($game);

        if ($forumIds->isEmpty()) {
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

    // NewsSourceRepositoryInterface

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
}
