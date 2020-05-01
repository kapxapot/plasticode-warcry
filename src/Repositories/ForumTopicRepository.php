<?php

namespace App\Repositories;

use App\Models\ForumTopic;
use App\Models\Game;
use App\Repositories\Interfaces\ForumTagRepositoryInterface;
use App\Repositories\Interfaces\ForumTopicRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;

class ForumTopicRepository extends IdiormRepository implements ForumTopicRepositoryInterface
{
    protected string $entityClass = ForumTopic::class;

    protected string $sortField = 'start_date';
    protected bool $sortReverse = true;

    protected ForumTagRepositoryInterface $forumTagRepository;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $repositoryContext,
        ForumTagRepositoryInterface $forumTagRepository,
        $hydrator = null
    )
    {
        parent::__construct($repositoryContext, $hydrator);

        $this->forumTagRepository = $forumTagRepository;
    }

    public function getByTag(string $tag) : Query
    {
        return self::filterByTag(self::query(), $tag);
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

    // queries

    private function newsQuery(?Game $game = null) : Query
    {
        $forumIds = Game::getNewsForumIds($game);

        if ($forumIds->isEmpty()) {
            return Query::empty();
        }
        
        return self::query()
            ->whereIn('forum_id', $forumIds);
    }

    // filters

    public function filterByTag(Query $query, string $tag) : Query
    {
        $ids = $this->forumTagRepository->getForumTopicIdsByTag($tag);

        return $query->whereIn($this->idField(), $ids);
    }
}
