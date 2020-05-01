<?php

namespace App\Repositories;

use App\Collections\EventCollection;
use App\Models\Event;
use App\Models\Game;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Plasticode\Query;
use Webmozart\Assert\Assert;

class EventRepository extends NewsSourceRepository implements EventRepositoryInterface
{
    /**
     * Auto-end interval in seconds (24 hours by default)
     */
    protected const AUTO_END_INTERVAL = 24 * 60 * 60;

    protected string $entityClass = Event::class;

    public function getProtected(?int $id) : ?Event
    {
        return $this->getProtectedEntity($id);
    }

    public function getAllOrderedByStart() : EventCollection
    {
        return EventCollection::from(
            $this->orderedByStartQuery()
        );
    }

    public function getAllUnended() : EventCollection
    {
        return EventCollection::from(
            $this->unendedQuery()
        );
    }

    public function getAllCurrent(?Game $game, int $days) : EventCollection
    {
        return EventCollection::from(
            $this
                ->unendedQuery()
                ->apply(
                    fn (Query $q) => $this->filterByGameTree($q, $game),
                    fn (Query $q) => $this->filterCurrent($q, $days),
                    fn (Query $q) => $this->filterAnnounced($q)
                )
        );
    }

    public function getAllFutureImportant() : EventCollection
    {
        return EventCollection::from(
            $this
                ->orderedByStartQuery()
                ->apply(
                    fn (Query $q) => $this->filterFuture($q)
                )
                ->where('important', 1)
        );
    }

    // SearchableRepositoryInterface

    public function search(string $searchQuery) : EventCollection
    {
        return EventCollection::from(
            $this
                ->publishedQuery()
                ->search($searchQuery, '(name like ?)')
                ->orderByAsc('name')
        );
    }

    // queries

    protected function unendedQuery() : Query
    {
        return $this
            ->orderedByStartQuery()
            ->apply(
                fn (Query $q) => $this->filterUnended($q)
            );
    }

    protected function orderedByStartQuery() : Query
    {
        return $this
            ->publishedQuery()
            ->orderByAsc('starts_at')
            ->thenByAsc('ends_at');
    }

    protected function newsSourceQuery() : Query
    {
        return $this->announcedQuery();
    }

    /**
     * Published + announced query.
     */
    protected function announcedQuery() : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterAnnounced($q)
            );
    }

    // filters

    /**
     * Filters events that are not ended yet or don't have an end.
     */
    protected function filterUnended(Query $query) : Query
    {
        return $query
            ->whereRaw(
                '(coalesce(ends_at, date_add(date(starts_at), interval ' . self::AUTO_END_INTERVAL . ' - 1 second)) >= now() or unknown_end = 1)'
            );
    }

    protected function filterCurrent(Query $query, int $days) : Query
    {
        Assert::greaterThan($days, 0);

        return $query
            ->whereRaw(
                '(starts_at < date_add(now(), interval ' . $days . ' day) or important = 1)'
            );
    }

    protected function filterFuture(Query $query) : Query
    {
        return $query->whereRaw('(starts_at > now())');
    }

    protected function filterAnnounced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
}
