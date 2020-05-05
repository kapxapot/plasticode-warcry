<?php

namespace App\Services;

use App\Collections\StreamStatCollection;
use App\Config\Interfaces\StreamConfigInterface;
use App\Models\Stream;
use App\Models\StreamStat;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\StreamStatRepositoryInterface;
use Plasticode\Collections\Basic\Collection;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

class StreamStatService
{
    private GameRepositoryInterface $gameRepository;
    private StreamStatRepositoryInterface $streamStatRepository;

    private GameService $gameService;

    private StreamConfigInterface $config;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        StreamStatRepositoryInterface $streamStatRepository,
        GameService $gameService,
        StreamConfigInterface $config
    )
    {
        $this->gameRepository = $gameRepository;
        $this->streamStatRepository = $streamStatRepository;

        $this->gameService = $gameService;

        $this->config = $config;
    }

    public function build(Stream $stream) : array
    {
        $stats = [];

        $streamId = $stream->id;

        $byGame = $this
            ->streamStatRepository
            ->getLatestWithGameByStream(
                $stream,
                $this->config->streamAnalysisPeriod()
            )
            ->groupByGame();

        $games = array_map(
            fn (string $key, StreamStatCollection $value) =>
            [
                'remote_game' => $key,
                'count' => $value->count(),
            ],
            array_keys($byGame),
            $byGame
        );

        if (!empty($games)) {
            $total = 0;

            foreach ($games as $game) {
                $total += $game['count'];
            }

            $games = array_map(
                function ($game) use ($total) {
                    $game['percent'] = ($total > 0)
                        ? round($game['count'] * 100 / $total, 1)
                        : 0;

                    $game['priority'] =
                        $this->gameService->isPriorityGame(
                            $game['remote_game']
                        );

                    return $game;
                },
                $games
            );

            $games = Sort::byMany(
                $games,
                SortStep::desc('priority'),
                SortStep::desc('percent')
            );

            $blizzardTotal = 0;

            foreach ($games as $game) {
                if ($game['priority']) {
                    $blizzardTotal += $game['percent'];
                }
            }

            $stats['games'] = $games;
            $stats['blizzard_total'] = $blizzardTotal;

            $stats['blizzard'] = [
                ['value' => $blizzardTotal, 'label' => 'Игры Blizzard'],
                ['value' => 100 - $blizzardTotal, 'label' => 'Другие игры']
            ];
        }

        $now = new \DateTime;
        $start = Date::startOfHour($now)->modify('-23 hour');

        $lastDayStats = $this
            ->streamStatRepository
            ->getAllFromDateByStream($stream, $start)
            ->map(
                function (StreamStat $s) {
                    $cr = strtotime($s->createdAt);

                    return [
                        ...$s->toArray(),
                        'stamp' => strftime('%d-%H', $cr),
                        'iso' => Date::formatIso($cr),
                    ];
                }
            );

        if ($lastDayStats->any()) {
            $stats['viewers'] = $this->buildGameStats(
                $lastDayStats,
                $start,
                $now
            );
        }

        $utc = Date::utc();
        $monthStartUtc = Date::startOfDay($utc)
            ->modify('-1 month')
            ->modify('1 day');

        $monthStart = Date::fromUtc($monthStartUtc);

        $lastMonthStats = $this
            ->streamStatRepository
            ->getAllFromDateByStream($stream, $monthStart)
            ->map(
                function (StreamStat $s) {
                    $utcCreatedAt = Date::utc(Date::dt($s->createdAt));

                    return [
                        ...$s->toArray,
                        'display_remote_status' => $s->displayRemoteStatus(),
                        'stamp' => $utcCreatedAt->format('m-d'),
                    ];
                }
            );

        if ($lastMonthStats->any()) {
            $stats['daily'] = $this->buildDailyStats(
                $lastMonthStats,
                $monthStart,
                $now
            );

            $stats['logs'] = $this->buildLogs($lastMonthStats);
        }

        return $stats;
    }

    private function buildGameStats(
        Collection $latest,
        \DateTime $start,
        \DateTime $end
    ) : array
    {
        $gamely = [];

        $prev = null;
        $prevGame = null;

        $set = [];

        $closeSet = function ($game) use (&$gamely, &$set) {
            if (!empty($set)) {
                if (!array_key_exists($game, $gamely)) {
                    $gamely[$game] = [];
                }

                $gamely[$game][] = $set;
                $set = [];
            }
        };

        foreach ($latest as $s) {
            $game = $s['remote_game'];

            if ($prev) {
                $exceeds = Date::exceedsInterval(
                    $prev['created_at'],
                    $s['created_at'],
                    'PT30M'
                ); // 30 minutes

                if ($exceeds) {
                    $closeSet($prevGame);
                } elseif ($prevGame != $game) {
                    $closeSet($prevGame);

                    $prev['remote_game'] = $game;
                    $set[] = $prev;
                }
            }

            $set[] = $s;

            $prev = $s;
            $prevGame = $game;
        }

        $closeSet($prevGame);

        return [
            'data' => $gamely,
            'min_date' => Date::formatIso($start),
            'max_date' => Date::formatIso($end),
        ];
    }

    private function buildDailyStats(
        Collection $latest,
        \DateTime $start,
        \DateTime $end
    ) : array
    {
        $daily = [];

        $cur = clone $start;

        while ($cur < $end) {
            $utcCur = Date::utc($cur);
            $stamp = $utcCur->format('m-d');

            $slice = $latest->where('stamp', $stamp);

            $peak = 0;
            $peakStatus = null;

            if (!empty($slice)) {
                foreach ($slice as $stat) {
                    $peak = max($stat['remote_viewers'], $peak);
                    $peakStatus = $stat['display_remote_status'];
                }
            }

            $daily[] = [
                'day' => $utcCur->format('M j'),
                'week_day' => $utcCur->format('D, M j'),
                'date' => Date::iso($utcCur),
                'peak_viewers' => $peak,
                'peak_status' => $peakStatus,
            ];

            $cur->modify('+1 day');
        }

        return $daily;
    }

    private function buildLogs(Collection $stats) : array
    {
        $logs = [];

        $add = function (array $log) use (&$logs) {
            $log['start_iso'] = Date::formatIso(Date::dt($log['created_at']));
            $log['end_iso'] = Date::formatIso(Date::dt($log['finished_at']));

            $log['game'] = $this
                ->gameRepository
                ->getByTwitchName($log['remote_game']);

            $logs[] = $log;
        };

        foreach ($stats as $stat) {
            $stat['created_cmp'] = strtotime($stat['created_at']);

            if (!isset($cur)) {
                $cur = $stat;
            } else {
                $exceeds = Date::exceedsInterval(
                    $cur['finished_at'],
                    $stat['created_at'],
                    '5 minutes'
                );

                if (
                    $cur['remote_game'] == $stat['remote_game']
                    && $cur['remote_status'] == $stat['remote_status']
                    && !$exceeds
                ) {
                    $cur['finished_at'] = $stat['finished_at'];
                } else {
                    $add($cur);
                    $cur = $stat;
                }
            }
        }

        if (isset($cur)) {
            $add($cur);
        }

        $logs = Sort::desc($logs, 'created_cmp');

        return $logs;
    }

    public function finishStat(StreamStat $stat) : StreamStat
    {
        $stat->finishedAt = Date::dbNow();

        return $this->streamStatRepository->save($stat);
    }
}
