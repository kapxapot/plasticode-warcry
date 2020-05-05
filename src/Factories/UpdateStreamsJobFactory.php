<?php

namespace App\Factories;

use App\Core\Interfaces\LinkerInterface;
use App\Jobs\UpdateStreamsJob;
use App\Repositories\Interfaces\StreamRepositoryInterface;
use App\Repositories\Interfaces\StreamStatRepositoryInterface;
use App\Services\StreamStatService;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Psr\Log\LoggerInterface;

class UpdateStreamsJobFactory
{
    private \Closure $maker;

    public function __construct(
        SettingsProviderInterface $settingsProvider,
        CacheInterface $cache,
        LinkerInterface $linker,
        Twitch $twitch,
        Telegram $telegram,
        LoggerInterface $logger,
        StreamRepositoryInterface $streamRepository,
        StreamStatRepositoryInterface $streamStatRepository,
        StreamStatService $streamStatService
    )
    {
        $this->maker = fn (bool $notify) =>
            new UpdateStreamsJob(
                $settingsProvider,
                $cache,
                $linker,
                $twitch,
                $telegram,
                $logger,
                $streamRepository,
                $streamStatRepository,
                $streamStatService,
                $notify
            );
    }

    public function make(bool $notify) : UpdateStreamsJob
    {
        return ($this->maker)($notify);
    }
}
