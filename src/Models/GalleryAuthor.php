<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\Publish;
use Plasticode\Util\Strings;

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
					'display_name' => [ 'dir' => 'asc', 'type' => 'string' ],
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
	    return self::getPublished()
	        ->where('category_id', $categoryId)
	        ->all();
    }

	// GETTERS - ONE

	public static function getPublishedByAlias($alias)
	{
		return self::getPublished()
    		->whereAnyIs([
                [ 'alias' => $alias ],
                [ 'id' => $alias ],
            ])
            ->one();
	}

    // PROPS
    
    public function category()
    {
        return GalleryAuthorCategory::get($this->categoryId);
    }
    
    public function url()
    {
        return $this->pageUrl();
    }
    
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

    private function getSiblings() : Query
    {
        return self::getBasePublished();
            //->where('category_id', $this->category()->getId());
    }
    
	public function prev()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		return self::getSiblings()
    		    ->all()
    		    ->descStr('display_name')
    		    ->where(function ($item) {
    		        return Strings::compare($item->displayName(), $this->displayName()) < 0;
                })
                ->first();
	    });
	}
	
	public function next()
	{
	    return $this->lazy(__FUNCTION__, function () {
    		return self::getSiblings()
    		    ->all()
    		    ->ascStr('display_name')
    		    ->where(function ($item) {
    		        return Strings::compare($item->displayName(), $this->displayName()) > 0;
                })
                ->first();
	    });
	}
    
    /**
     * Returns author's pictures, sorted in REVERSE chronological order.
     */
    public function pictures() : Query
    {
        return GalleryPicture::getPublishedByAuthor($this->id);
    }
    
    public function count()
    {
        return $this->pictures()->count();
    }
    
    public function latestPicture()
    {
        // sorted in reverse, so first
        return $this->pictures()->one();
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
