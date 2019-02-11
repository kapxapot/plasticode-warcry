<?php

namespace App\Models;

use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;

class GalleryPicture extends DbModel
{
    use Description, FullPublish, Stamps, Tags;
    
    protected static $sortOrder = [
        [ 'field' => 'published_at', 'reverse' => true ],
        [ 'field' => 'id', 'reverse' => true ],
    ];

    // GETTERS - MANY

    public static function getPublishedByAuthor($authorId, $offset, $limit)
    {
        return self::getAllPublished(function ($q) use ($authorId, $offset, $limit) {
			$q = $q->where('author_id', $authorId);
			
			if ($limit > 0) {
				$q = $q
					->offset($offset)
					->limit($limit);
			}
			
			return $q;
		});
    }
    
	public static function getLatestByGame($game, $limit)
	{
		return self::getAllPublished(function ($q) use ($game, $limit) {
		    if ($game) {
			    $q = $game->filter($q);
		    }

			if ($limit > 0) {
				$q = $q
					->offset(0)
					->limit($limit);
			}
			
			return $q;
		});
	}

    // PROPS

    public function game()
    {
        return $this->gameId
            ? Game::get($this->gameId)
            : null;
    }
    
    public function ext()
    {
		return self::$linker->getExtension($this->pictureType);
    }

    public function url()
    {
        return self::$linker->galleryPictureImg($this);
    }

    public function thumbUrl()
    {
        return self::$linker->galleryThumbImg($this);
    }
    
    public function author()
    {
        return GalleryAuthor::get($this->authorId);
    }
    
    public function pageUrl()
    {
	    return self::$linker->galleryPicture($this->author()->alias, $this->id);
	}
	
	public function prev()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		return self::getPublishedWhere(function ($q) {
    			return $q
    				->where('author_id', $this->authorId)
    				->whereGt('published_at', $this->publishedAt)
    				->orderByAsc('published_at')
    				->orderByAsc('id');
    		});
	    });
	}
	
	public function next()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		return self::getPublishedWhere(function ($q) {
    			return $q
    				->where('author_id', $this->authorId)
    				->whereLt('published_at', $this->publishedAt)
    				->orderByDesc('published_at')
    				->orderByDesc('id');
    		});
		});
    }
}
