<?php

namespace App\Services;

use App\Collections\StreamCollection;
use App\Config\Interfaces\StreamConfigInterface;
use App\Models\Game;
use App\Models\Stream;
use App\Repositories\Interfaces\StreamRepositoryInterface;
use Plasticode\Util\Cases;
use Plasticode\Util\Date;

class StreamService
{
    private StreamRepositoryInterface $streamRepository;

    private StreamConfigInterface $config;
    private Cases $cases;

    public function __construct(
        StreamRepositoryInterface $streamRepository,
        StreamConfigInterface $config,
        Cases $cases
    )
    {
        $this->streamRepository = $streamRepository;

        $this->config = $config;
        $this->cases = $cases;
    }

    /**
     * @return StreamCollection[]
     */
    public function getArrangedByTag(string $tag) : array
    {
        return $this
            ->streamRepository
            ->getAllByTag($tag)
            ->sort()
            ->arrange();
    }

    public function getGroups() : array
    {
        return $this
            ->getAllSorted()
            ->groupByTabs();
    }

    public function getAllOnline(?Game $game = null) : StreamCollection
    {
        $online = $this
            ->getAllSorted()
            ->where(
                fn (Stream $s) => $s->isOnline()
            );

        if ($game) {
            $online = $online->where(
                fn (Stream $s) => $s->belongsToGame($game)
            );
        }

        return $online;
    }

    public function getAllSorted() : StreamCollection
    {
        return $this
            ->streamRepository
            ->getAllPublished()
            ->sort();
    }

    public function topOnline(?Game $game = null) : ?Stream
    {
        $stream = null;

        if ($game && !$game->isDefault()) {
            $stream = $this->getAllOnline($game)->first();
        }

        return $stream ?? $this->getAllOnline()->first();
    }

    public function totalOnlineStr(?Game $game = null) : string
    {
        $totalOnline = $this->getAllOnline($game)->count();

        return $totalOnline . ' '
            . $this->cases->caseForNumber('стрим', $totalOnline);
    }

    public function isAlive(Stream $stream) : bool
    {
        if (!$stream->remoteOnlineAt) {
            return false;
        }

        $timeToLive = $this->config->streamTimeToLive();
        $age = Date::age($stream->remoteOnlineAt);

        return $age->days < $timeToLive;
    }

    public function nounsFor(Stream $stream) : array
    {
        return [
            'viewers' => $this->cases->caseForNumber(
                'зритель', $stream->remoteViewers
            ),
        ];
    }

    public function verbsFor(Stream $stream) : array
    {
        $form = [
            'time' => Cases::PAST,
            'person' => Cases::FIRST,
            'number' => Cases::SINGLE,
            'gender' => $stream->genderId,
        ];

        return [
            'played' => $this->cases->conjugation(
                'играть',
                $form
            ),
            'broadcasted' => $this->cases->conjugation(
                'транслировать',
                $form
            ),
            'held' => $this->cases->conjugation(
                'вести',
                $form
            ),
        ];
    }
}
