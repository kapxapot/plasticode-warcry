<?php

namespace App\Services;

use App\Models\Stream;
use App\Models\StreamStat;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;
use Plasticode\Util\SortStep;

class StreamStatService
{
    /** @var GameRepositoryInterface */
    private $gameRepository;

    /** @var GameService */
    private $gameService;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        GameService $gameService
    )
    {
        $this->gameRepository = $gameRepository;
        $this->gameService = $gameService;
    }

    public function build(Stream $stream)
    {
        $stats = [];
        
        $streamId = $stream->id;
        
        $games = StreamStat::getGames($streamId)->toArray();
        
        if (!empty($games)) {
            $total = 0;
            foreach ($games as $game) {
                $total += $game['count'];
            }
            
            $games = array_map(
                function($game) use ($total) {
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

            $steps = [
                SortStep::createDesc('priority'),
                SortStep::createDesc('percent'),
            ];

            $games = Sort::multi($games, $steps);

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

        $lastDayStats = StreamStat::getFrom($streamId, $start)
            ->map(
                function ($s) {
                    $cr = strtotime($s->createdAt);
                    $s->stamp = strftime('%d-%H', $cr);
                    $s->iso = Date::formatIso($cr);
                    return $s;
                }
            );

        if ($lastDayStats->any()) {
            $stats['viewers'] = $this->buildGameStats(
                $lastDayStats, $start, $now
            );
        }
        
        $utc = Date::utc();
        $monthStartUtc = Date::startOfDay($utc)
            ->modify('-1 month')
            ->modify('1 day');
        
        $monthStart = Date::fromUtc($monthStartUtc);
        
        $lastMonthStats = StreamStat::getFrom($streamId, $monthStart)
            ->map(
                function ($s) {
                    $utcCreatedAt = Date::utc(Date::dt($s->createdAt));
                    $s->stamp = $utcCreatedAt->format('m-d');
                    return $s;
                }
            );
            
        if ($lastMonthStats->any()) {
            $stats['daily'] = $this->buildDailyStats(
                $lastMonthStats, $monthStart, $now
            );

            $stats['logs'] = $this->buildLogs($lastMonthStats);
        }

        return $stats;
    }
    
    private function buildGameStats($latest, \DateTime $start, \DateTime $end)
    {
        $gamely = [];
        
        $prev = null;
        $prevGame = null;
        
        $set = [];
        
        $closeSet = function($game) use (&$gamely, &$set) {
            if (!empty($set)) {
                if (!array_key_exists($game, $gamely)) {
                    $gamely[$game] = [];
                }

                $gamely[$game][] = $set;
                $set = [];
            }
        };
        
        foreach ($latest as $s) {
            $game = $s->remoteGame;
            
            if ($prev) {
                $exceeds = Date::exceedsInterval(
                    $prev->createdAt, $s->createdAt, 'PT30M'
                ); // 30 minutes

                if ($exceeds) {
                    $closeSet($prevGame);
                } elseif ($prevGame != $game) {
                    $closeSet($prevGame);

                    $prev->remoteGame = $game;
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

    private function buildDailyStats($latest, \DateTime $start, \DateTime $end)
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
                    $peak = max($stat->remoteViewers, $peak);
                    $peakStatus = $stat->displayRemoteStatus();
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

    private function buildLogs($stats)
    {
        $logs = [];

        $add = function ($log) use (&$logs) {
            $log->startIso = Date::formatIso(Date::dt($log->createdAt));
            $log->endIso = Date::formatIso(Date::dt($log->finishedAt));
            $log->game = $this->gameRepository->getByTwitchName($log->remoteGame);

            $logs[] = $log;
        };

        foreach ($stats as $stat) {
            $stat->createdCmp = strtotime($stat->createdAt);

            if (!isset($cur)) {
                $cur = $stat;
            } else {
                $exceeds = Date::exceedsInterval(
                    $cur->finishedAt, $stat->createdAt, '5 minutes'
                );
                
                if ($cur->remoteGame == $stat->remoteGame &&
                    $cur->remoteStatus == $stat->remoteStatus &&
                    !$exceeds) {
                    $cur->finishedAt = $stat->finishedAt;
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
}
