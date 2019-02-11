<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\Publish;

class GalleryAuthor extends DbModel
{
    use Description, Publish;

	public static function getGroups()
	{
		$groups = [];
		
		$cats = GalleryAuthorCategory::getAll();
		
		foreach ($cats as $cat) {
			if ($cat->authors()->any()) {
				$sorts = [
					//'count' => [ 'dir' => 'desc' ],
					'name' => [ 'dir' => 'asc', 'type' => 'string' ],
				];
		
				$groups[] = [
					'id' => $cat->alias,
					'label' => $cat->name,
					'values' => $cat->authors()->multiSort($sorts),
				];
			}
		}

		return Collection::make($groups);
	}
	
	// GETTERS - MANY
	
	public static function getAllPublishedByCategory($categoryId)
	{
	    return self::getAllPublished(
	        self::where('category_id', $categoryId)
        );
    }

	// GETTERS - ONE

	public static function getPublishedByAlias($alias)
	{
		return self::getPublishedWhere(function($q) use ($alias) {
    		return $q->whereAnyIs([
                [ 'alias' => $alias ],
                [ 'id' => $alias ],
            ]);
		});
	}

    // PROPS
    
    public function pageUrl()
    {
        return self::$linker->galleryAuthor($this->alias);
    }
    
    public function displayName()
    {
		return $this->realName ?? $this->realNameEn ?? $this->name;
    }
    
    public function subname()
    {
        return $this->name != $this->displayName()
            ? $this->name
            : null;
    }
    
    public function fullName()
    {
        $fullName = $this->displayName();
        
        if ($this->subname()) {
            $fillName .= " ({$this->subname()})";
        }
        
        return $fullName;
    }
    
    /**
     * Returns author's pictures, sorted in REVERSE chronological order.
     */
    public function pictures($offset = 0, $limit = 0)
    {
        return GalleryPicture::getPublishedByAuthor($this->id, $offset, $limit);
    }
    
    public function count()
    {
        return $this->pictures()->count();
    }
    
    public function latestPicture()
    {
        // sorted in reverse, so first
        return $this->pictures()->first();
    }
    
    public function displayPicture()
    {
        return $this->latestPicture();
    }
    
    public function picturesStr()
    {
		return $this->count() . ' ' . self::$cases->caseForNumber('картинка', $this->count());
    }
    
    public function forumMember()
    {
        return ForumMember::getByName($this->name);
    }
}
