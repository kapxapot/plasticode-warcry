<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ForumPost extends DbModel
{
	public static function getByForumTopic($topicId)
	{
		return self::getBy(function($q) use ($topicId) {
			return $q
				->where('topic_id', $topicId)
				->where('new_topic', 1);
		});
	}
}
