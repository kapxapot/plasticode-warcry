<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Util\Date;

class StreamStat extends DbModel
{
    // getters - one
    
	public static function getLast($streamId)
	{
    	return self::getBy(function($q) use ($streamId) {
			return $q
			    ->where('stream_id', $streamId)
			    ->orderByDesc('created_at');
		});
	}

	// event overrides
	
	protected function beforeSave()
	{
	    parent::beforeSave();
	    
		$this->remoteStatus = urlencode($this->remoteStatus);
	}

	protected function afterMake()
	{
	    parent::afterMake();
	    
		$this->remoteStatus = urldecode($this->remoteStatus);
	}

    // methods

	public function finish()
	{
		$this->finishedAt = Date::dbNow();
		$this->save();
	}
	
	// getters - many
	
	public static function getGames($streamId, $days = 30)
	{
		$table = static::getTable();

	    return self::getRaw(
	        "select remote_game, count(*) count
			from {$table}
			where created_at >= date_sub(now(), interval {$days} day) and length(remote_game) > 0 and stream_id = :stream_id
			group by remote_game",
			[
			    'stream_id' => intval($streamId),
			]
		);
	}
	
	/*public static function getLatestStats($streamId, $days = 1)
	{
		return $this->getMany(function($q) use ($streamId, $days) {
			$table = $this->getTable();
			
			return $q
				->rawQuery(
					"select *
					from {$table}
					where created_at >= date_sub(now(), interval {$days} day) and length(remote_game) > 0 and stream_id = :stream_id",
					[ 'stream_id' => intval($streamId) ])
				->orderByAsc('created_at');
		});
	}*/
	
	public static function getFrom($streamId, \DateTime $from)
	{
		return self::getMany(function($q) use ($streamId, $from) {
			return $q
				->where('stream_id', $streamId)
				->whereGte('created_at', Date::formatDb($from))
				->orderByAsc('created_at');
		});
	}
}
