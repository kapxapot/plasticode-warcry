<?php

namespace App\Models;

use Plasticode\Models\DbModel;

class ForumTag extends DbModel
{
    // getters - many
    
	public static function getByForumTopic($topicId)
	{
		return self::getMany(function($q) use ($topicId) {
			return $q
				->where('tag_meta_app', 'forums')
				->where('tag_meta_area', 'topics')
				->where('tag_meta_id', $topicId);
		});
	}
	
	public static function getForumTopicIdsByTag($tag)
	{
		return self::getMany(function($q) use ($tag) {
			return $q
				->where('tag_meta_app', 'forums')
				->where('tag_meta_area', 'topics')
				->whereRaw('(lcase(tag_text) = ?)', [ $tag ]);
		})
		->extract('tag_meta_id');
	}
}
