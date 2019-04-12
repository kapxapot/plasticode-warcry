<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;

class Forum extends DbModel
{
    // queries
    
	public static function getAllByGame($gameId) : Collection
	{
		$result = Collection::make();

		$forums = self::getAll();

		foreach ($forums as $forum) {
			$game = Game::getByForumId($forum->getId());
			
			if ($game->getId() == $gameId) {
				$result = $result->add($forum);
			}
		}
		
		return $result;
	}
	
	// props
	
	public function isNewsForum()
	{
        return Game::getNewsForumIds()
            ->contains($this->getId());
	}
}
