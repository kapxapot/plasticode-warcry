<?php

namespace App\Models;

use Plasticode\Collections\Basic\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\Published;
use Plasticode\Util\Strings;

/**
 * @property string $alias
 * @property integer $categoryId
 * @property string $name
 * @property string|null $realName
 * @property string|null $realNameEn
 */
class GalleryAuthor extends DbModel
{
    use Description;
    use Published;

    public static function getGroups() : Collection
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
                    'values' => $cat->authors()->sort(...$sorts),
                ];
            }
        }

        return Collection::make($groups);
    }
    
    // GETTERS - MANY
    
    public static function getAllPublishedByCategory($categoryId) : Collection
    {
        return self::getPublished()
            ->where('category_id', $categoryId)
            ->all();
    }

    // GETTERS - ONE

    public static function getPublishedByAlias($alias) : ?self
    {
        return self::getPublished()
            ->whereAnyIs(
                [
                    ['alias' => $alias],
                    ['id' => $alias],
                ]
            )
            ->one();
    }

    // PROPS
    
    public function category() : GalleryAuthorCategory
    {
        return GalleryAuthorCategory::get($this->categoryId);
    }
    
    public function url() : string
    {
        return $this->pageUrl();
    }
    
    public function pageUrl() : string
    {
        return self::$container->linker->galleryAuthor($this);
    }
    
    public function displayName() : string
    {
        return $this->realName ?? $this->realNameEn ?? $this->name;
    }
    
    public function subname() : ?string
    {
        return $this->name != $this->displayName()
            ? $this->name
            : null;
    }
    
    public function fullName() : string
    {
        $fullName = $this->displayName();
        
        if ($this->subname()) {
            $fullName .= ' (' . $this->subname() . ')';
        }
        
        return $fullName;
    }

    private function getSiblings() : Query
    {
        return self::getPublished();
    }
    
    public function prev() : ?self
    {
        return $this->lazy(
            function () {
                return self::getSiblings()
                    ->all()
                    ->descStr('display_name')
                    ->where(
                        function ($item) {
                            return Strings::compare(
                                $item->displayName(),
                                $this->displayName()
                            ) < 0;
                        }
                    )
                    ->first();
            }
        );
    }
    
    public function next() : ?self
    {
        return $this->lazy(
            function () {
                return self::getSiblings()
                    ->all()
                    ->ascStr('display_name')
                    ->where(
                        function ($item) {
                            return Strings::compare(
                                $item->displayName(),
                                $this->displayName()
                            ) > 0;
                        }
                    )
                    ->first();
            }
        );
    }
    
    /**
     * Returns author's pictures, sorted in REVERSE chronological order.
     */
    public function pictures() : Query
    {
        return GalleryPicture::getPublishedByAuthor($this->id);
    }
    
    public function count() : int
    {
        return $this->pictures()->count();
    }
    
    public function latestPicture() : ?GalleryPicture
    {
        // sorted in reverse, so first
        return $this->pictures()->one();
    }
    
    public function displayPicture() : ?GalleryPicture
    {
        return $this->latestPicture();
    }
    
    public function picturesStr() : string
    {
        return $this->count() . ' ' . self::$container->cases->caseForNumber('картинка', $this->count());
    }
    
    public function forumMember() : ?ForumMember
    {
        return ForumMember::getByName($this->name);
    }
}
