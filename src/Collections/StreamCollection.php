<?php

namespace App\Collections;

use App\Models\Stream;
use Plasticode\Collections\Basic\TaggedCollection;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

class StreamCollection extends TaggedCollection
{
    protected string $class = Stream::class;

    public function sort() : self
    {
        return $this->sortBy(
            SortStep::desc('remote_online'),
            SortStep::desc('official_ru'),
            SortStep::desc('official'),
            SortStep::desc('priority'),
            SortStep::desc('is_top'),
            SortStep::desc('remote_viewers'),
            SortStep::desc('remote_online_at', Sort::STRING),
            SortStep::desc('title', Sort::STRING)
        );
    }

    /**
     * Splits collection in 3 parts, excluding empty ones:
     * 
     * - online
     * - offline with logo
     * - offline without logo
     *
     * @return static[]
     */
    public function arrange() : array
    {
        return array_filter(
            [
                $this->where(
                    fn (Stream $s) => $s->isOnline()
                ),
                $this->where(
                    fn (Stream $s) => !$s->isOnline() && $s->hasLogo()
                ),
                $this->where(
                    fn (Stream $s) => !$s->isOnline() && !$s->hasLogo()
                ),
            ],
            fn (self $c) => !$c->isEmpty()
        );
    }

    public function groupByTabs() : array
    {
        $groups = [
            [
                'id' => 'online',
                'label' => 'Онлайн',
                'telegram' => 'warcry_streams',
                'streams' => $this->where(
                    fn (Stream $s) => $s->isOnline()
                ),
            ],
            [
                'id' => 'offline',
                'label' => 'Офлайн',
                'telegram' => 'warcry_streams',
                'streams' => $this->where(
                    fn (Stream $s) => $s->isAlive() && !$s->isOnline()
                ),
            ],
            [
                'id' => 'blizzard',
                'label' => 'Blizzard EN',
                'telegram' => 'blizzard_streams',
                'telegram_label' => 'официальных трансляций (англ.)',
                'streams' => $this->where(
                    fn (Stream $s) => $s->official
                ),
            ],
            [
                'id' => 'blizzard_ru',
                'label' => 'Blizzard РУ',
                'telegram' => 'blizzard_streams_ru',
                'telegram_label' => 'официальных трансляций (рус.)',
                'streams' => $this->where(
                    fn (Stream $s) => $s->officialRu
                ),
            ],
        ];

        return array_map(
            function (array $group) {
                /** @var self */
                $streams = $group['streams'];

                $group['streams'] = $streams->arrange();

                return $group;
            },
            $groups
        );
    }
}
