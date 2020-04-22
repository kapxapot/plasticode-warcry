<?php

namespace App\Services;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Article;
use App\Models\Event;
use App\Models\ForumTopic;
use App\Models\Game;
use App\Models\Interfaces\NewsSourceInterface;
use App\Models\News;
use App\Models\NewsYear;
use App\Models\Video;
use App\Repositories\Interfaces\NewsRepositoryInterface;
use Plasticode\Collection;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;
use Webmozart\Assert\Assert;

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

    private NewsRepositoryInterface $newsRepository;
    private LinkerInterface $linker;

    public function __construct(
        NewsRepositoryInterface $newsRepository,
        LinkerInterface $linker
    )
    {
        $this->newsRepository = $newsRepository;
        $this->linker = $linker;
    }

    /**
     * Get sources list based on the strictness.
     *
     * @return string[]
     */
    private function getSources(bool $strict = false) : array
    {
        return $strict
            ? $this->strictSources
            : $this->sources;
    }

    /**
     * Apply action to sources list.
     */
    private function withSources(bool $strict, \Closure $action) : array
    {
        return array_map($action, $this->getSources($strict));
    }

    /**
     * Returns action results as one collection.
     * Action must return a collection.
     */
    private function collect(bool $strict, \Closure $action) : Collection
    {
        return Collection::merge(...$this->withSources($strict, $action));
    }

    /**
     * Sorts news in descending order by publish date.
     * Reverse sort = ascending order.
     */
    private function sort(
        Collection $collection,
        bool $reverse = false
    ) : Collection
    {
        return $collection->orderBy(
            'published_at',
            $reverse ? Sort::DESC : Sort::ASC,
            Sort::DATE
        );
    }

    private function sortReverse(Collection $collection) : Collection
    {
        return $this->sort($collection, true);
    }

    public function getByTag(string $tag, bool $strict = true) : Collection
    {
        $all = $this->collect(
            $strict,
            fn ($s) => $s::getNewsByTag($tag)->all()
        );

        return $this->sort($all);
    }

    public function getCount(Game $game = null, bool $strict = false) : int
    {
        $counts = $this->withSources(
            $strict,
            fn ($s) => $s::getLatestNews($game)->count()
        );

        return array_sum($counts);
    }

    public function getLatest(
        ?Game $game,
        int $limit,
        ?int $exceptNewsId = null,
        bool $strict = true
    ) : Collection
    {
        return $this->getPage($game, 1, $limit, $exceptNewsId, $strict);
    }

    public function getPage(
        ?Game $game = null,
        int $page = 1,
        int $pageSize = 7,
        ?int $exceptNewsId = null,
        bool $strict = false
    ) : Collection
    {
        if ($page < 1) {
            $page = 1;
        }

        Assert::greaterThan($pageSize, 0);

        $offset = ($page - 1) * $pageSize;

        // optimization for faster load
        $loadLimit = $offset + $pageSize;

        $all = $this->collect(
            $strict,
            fn (string $s) =>
            $s::getLatestNews($game, $exceptNewsId)
                ->limit($loadLimit)
                ->all()
        );

        $all = $this->sort($all);

        return $all->slice($offset, $pageSize);
    }

    /**
     * Looks for News or ForumTopic with the provided id.
     */
    public function getNews(int $newsId) : ?NewsSourceInterface
    {
        $news = $this->newsRepository->getProtected($newsId);

        /** @var ForumTopic|null */
        $forumTopic = ForumTopic::getNews($newsId);

        return $news ?? $forumTopic;
    }

    public function getPrev(
        NewsSourceInterface $news,
        bool $strict = true
    ) : ?NewsSourceInterface
    {
        $date = $news->publishedAt;
        $game = $news->rootGame();

        $allPrev = $this->collect(
            $strict,
            fn (string $s) => $s::getNewsBefore($game, $date)->one()
        );

        $allPrev = $this->sort($allPrev);

        return $allPrev->first();
    }

    public function getNext(
        NewsSourceInterface $news,
        bool $strict = true
    ) : ?NewsSourceInterface
    {
        $date = $news->publishedAt;
        $game = $news->rootGame();

        $allNext = $this->collect(
            $strict,
            fn (string $s) => $s::getNewsAfter($game, $date)->one()
        );

        $allNext = $this->sortReverse($allNext);

        return $allNext->first();
    }

    private function getAllRaw(bool $strict = false) : Collection
    {
        return $this->collect(
            $strict,
            fn (string $s) => $s::getLatestNews()->all()
        );
    }

    public function getTop(int $limit, bool $strict = false) : Collection
    {
        return $this->getPage(null, 1, $limit, null, $strict);
    }

    /**
     * Descending.
     */
    public function getYears(bool $strict = true) : Collection
    {
        $byYear = self::getAllRaw($strict)
            ->group(
                fn (NewsSourceInterface $item) =>
                Date::year(
                    $item->publishedAtIso()
                )
            );

        /** @var integer[] */
        $years = array_keys($byYear);
        
        return Collection::make($years)
            ->map(
                fn (int $y) =>
                new NewsYear(
                    $y,
                    $this->linker->newsYear($y)
                )
            )
            ->desc('year');
    }

    public function getPrevYear(int $year, bool $strict = true) : ?NewsYear
    {
        return $this->getYears($strict)
            ->where(
                fn (NewsYear $y) => $y->year < $year
            )
            ->desc('year')
            ->first();
    }

    public function getNextYear(int $year, bool $strict = true) : ?NewsYear
    {
        return $this->getYears($strict)
            ->where(
                fn (NewsYear $y) => $y->year > $year
            )
            ->asc('year')
            ->first();
    }

    public function getByYear(int $year, bool $strict = true) : array
    {
        $byYear = $this->collect(
            $strict,
            fn (string $s) => $s::getNewsByYear($year)->all()
        );

        $sorted = $this->sort($byYear);

        $monthly = [];

        /** @var NewsSourceInterface $s */
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
