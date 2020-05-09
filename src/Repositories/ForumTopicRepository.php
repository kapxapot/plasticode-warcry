<?php

namespace App\Repositories;

use App\Collections\ForumTopicCollection;
use App\Models\ForumTopic;
use App\Models\Game;
use App\Repositories\Interfaces\ForumTagRepositoryInterface;
use App\Repositories\Interfaces\ForumTopicRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;

class ForumTopicRepository extends IdiormRepository implements ForumTopicRepositoryInterface
{
    protected string $entityClass = ForumTopic::class;

    protected string $sortField = 'start_date';
    protected bool $sortReverse = true;

    private ForumTagRepositoryInterface $forumTagRepository;
    private GameRepositoryInterface $gameRepository;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $repositoryContext,
        ForumTagRepositoryInterface $forumTagRepository,
        GameRepositoryInterface $gameRepository,
        $hydrator = null
    )
    {
        parent::__construct($repositoryContext, $hydrator);

        $this->forumTagRepository = $forumTagRepository;
        $this->gameRepository = $gameRepository;
    }

    public function get(?int $id) : ?ForumTopic
    {
        return $this->getEntity($id);
    }

    // TaggedRepositoryInterface

    public function getAllByTag(string $tag, int $limit = 0) : ForumTopicCollection
    {
        return ForumTopicCollection::from(
            $this->filterByTag($this->query(), $tag, $limit)
        );
    }

    // NewsSourceRepositoryInterface

    public function getNewsByTag(string $tag, int $limit = 0) : ForumTopicCollection
    {
        return ForumTopicCollection::from(
            $this->filterByTag($this->newsQuery(), $tag, $limit)
        );
    }

    public function getLatestNews(
        ?Game $game = null,
        int $limit = 0,
        int $exceptId = 0
    ) : ForumTopicCollection
    {
        return ForumTopicCollection::from(
            $this
                ->newsQuery($game)
                ->applyIf(
                    $exceptId > 0,
                    fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
                )
                ->limit($limit)
        );
    }

    public function getNewsCount(?Game $game = null) : int
    {
        return $this
            ->newsQuery($game)
            ->count();
    }

    public function getNewsBefore(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : ForumTopicCollection
    {
        return ForumTopicCollection::from(
            $this
                ->newsQuery($game)
                ->whereLt('start_date', strtotime($date))
                ->orderByDesc('start_date')
        );
    }

    public function getNewsAfter(
        ?Game $game = null,
        string $date,
        int $limit = 0
    ) : ForumTopicCollection
    {
        return ForumTopicCollection::from(
            $this
                ->newsQuery($game)
                ->whereGt('start_date', strtotime($date))
                ->orderByAsc('start_date')
        );
    }

    public function getNewsByYear(int $year) : ForumTopicCollection
    {
        return ForumTopicCollection::from(
            $this
                ->newsQuery()
                ->whereRaw(
                    '(year(from_unixtime(start_date)) = ?)',
                    [$year]
                )
        );
    }

    public function getNews(?int $id) : ?ForumTopic
    {
        $topic = $this->get($id);

        return ($topic && $topic->isNews())
            ? $topic
            : null;
    }

    // queries

    protected function newsQuery(?Game $game = null) : Query
    {
        $forumIds = $this
            ->gameRepository
            ->getSubTreeOrAll($game)
            ->newsForums()
            ->ids();

        return $this
            ->query()
            ->whereIn('forum_id', $forumIds);
    }

    // filters

    protected function filterByTag(Query $query, string $tag, int $limit = 0) : Query
    {
        $ids = $this->forumTagRepository->getForumTopicIdsByTag($tag);

        return $query
            ->whereIn($this->idField(), $ids)
            ->limit($limit);
    }
}
