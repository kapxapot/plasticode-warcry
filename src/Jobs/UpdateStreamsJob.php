<?php

namespace App\Jobs;

use Plasticode\Contained;
use Plasticode\Util\Date;

use App\Models\Stream;
use App\Models\StreamStat;

class UpdateStreamsJob extends Contained
{
    private $notify;
    
    public function __construct($container, $notify)
    {
        parent::__construct($container);
        
        $this->notify = $notify;
    }
    
    public function run()
    {
		return Stream::getPublished()
		    ->all()
		    ->map(function ($s) {
		        return $this->updateStream($s);
		    });
    }
    
	private function updateStream(Stream $stream)
	{
		$id = $stream->streamId;
		
		switch ($stream->typeId) {
			// Twitch
			case 1:
				$data = $this->twitch->getStreamData($id);

				$s = $data['streams'][0] ?? null;

				if ($s) {
					$streamStarted = !$stream->isOnline();

					$stream->remoteOnline = 1;
					$stream->remoteGame = $s['game'];
					$stream->remoteViewers = $s['viewers'];
					
					if (isset($s['channel'])) {
						$ch = $s['channel'];

						$stream->remoteTitle = $ch['display_name'];
						$stream->remoteStatus = urlencode($ch['status']);
						$stream->remoteLogo = $ch['logo'];
					}
					
					if ($this->notify && $streamStarted) {
						$message = $this->sendStreamNotifications($stream);
					}
				}
				else {
					$stream->remoteOnline = 0;
					$stream->remoteViewers = 0;
				}
				
				break;
			
			default:
				throw new \Exception('Unsupported stream type: ' . $stream->typeId);
		}
		
		$now = Date::dbNow();
		
		$stream->remoteUpdatedAt = $now;

		if ($stream->remoteOnline == 1) {
			$stream->remoteOnlineAt = $now;
		}

		// save
		$stream->save();

		// stats
		$this->updateStreamStats($stream);
		
		return [
		    'stream' => $stream,
		    'json' => json_encode($data),
		    'message' => $message,
		];
	}
	
	private function updateStreamStats(Stream $stream)
	{
		$online = $stream->isOnline();
		$refresh = $online;
		
		$stats = StreamStat::getLast($stream->id);
		
		if ($stats) {
			if ($online) {
				$statsTTL = $this->getSettings('streams.stats_ttl');

				$expired = Date::expired($stats->createdAt, "PT{$statsTTL}M");
	
				if (!$expired && ($stream->remoteGame == $stats->remoteGame)) {
					$refresh = false;
				}
			}

			if (!$stats->finishedAt && (!$online || $refresh)) {
				$stats->finish();
			}
		}
		
		if ($refresh) {
	        $stats = StreamStat::fromStream($stream);
			$stats->save();
		}
	}
	
	private function sendStreamNotifications(Stream $s)
	{
		$verb = ($s->channel == 1)
			? ($s->remoteStatus
				? "транслирует <b>{$s->remoteStatus}</b>"
				: 'ведет трансляцию')
			: "играет в <b>{$s->remoteGame}</b>
{$s->remoteStatus}";

		$verbEn = ($s->channel == 1)
			? ($s->remoteStatus
				? "is streaming <b>{$s->remoteStatus}</b>"
				: 'started streaming')
			: "is playing <b>{$s->remoteGame}</b>
{$s->remoteStatus}";
		
		$url = $this->linker->twitch($s->streamId);
		$source = "<a href=\"{$url}\">{$s->title}</a>";
		
		$message = $source . ' ' . $verb;
		$messageEn = $source . ' ' . $verbEn;

		$settings = [
			/*[
				'channel' => 'warcry',
				'condition' => $s->priority == 1 || $s->official == 1 || $s->officialRu == 1,
				'message' => $message,
			],*/
			[
				'channel' => 'warcry_streams',
				'condition' => true,
				'message' => $message,
			],
			[
				'channel' => 'blizzard_streams',
				'condition' => $s->official == 1,
				'message' => $messageEn,
			],
			[
				'channel' => 'blizzard_streams_ru',
				'condition' => $s->officialRu == 1,
				'message' => $message,
			],
		];

		foreach ($settings as $setting) {
			if ($setting['condition']) {
				$this->telegram->sendMessage($setting['channel'], $setting['message']);
			}
		}

		return $message . ' ' . $messageEn;
	}
}
