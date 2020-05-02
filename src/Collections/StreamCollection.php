<?php

namespace App\Collections;

use App\Models\Stream;
use Plasticode\TypedCollection;

class StreamCollection extends TypedCollection
{
    protected string $class = Stream::class;

    public function sort() : self
    {
        $sorts = [
            'remote_online' => [ 'dir' => 'desc' ],
            'official_ru' => [ 'dir' => 'desc' ],
            'official' => [ 'dir' => 'desc' ],
            'priority' => [ 'dir' => 'desc' ],
            'is_top' => [ 'dir' => 'desc' ],
            'remote_viewers' => [ 'dir' => 'desc' ],
            'remote_online_at' => [ 'dir' => 'desc', 'type' => 'string' ],
            'title' => [ 'dir' => 'asc', 'type' => 'string' ],
        ];

        return $this->multiSort($sorts);
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
