<?php

namespace App\Models;

use App\Collections\GalleryPictureCollection;
use App\Models\Traits\Description;
use App\Models\Traits\PageUrl;
use App\Models\Traits\Stamps;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\Published;

/**
 * @property string|null $alias
 * @property string|null $artStation
 * @property integer $categoryId
 * @property string|null $deviant
 * @property string $name
 * @property string|null $realName
 * @property string|null $realNameEn
 * @method GalleryAuthorCategory category()
 * @method ForumMember|null forumMember()
 * @method GalleryPictureCollection pictures()
 * @method static withCategory(GalleryAuthorCategory|callable $category)
 * @method static withForumMember(ForumMember|callable|null $forumMember)
 * @method static withPictures(GalleryPictureCollection|callable $pictures)
 */
class GalleryAuthor extends DbModel implements LinkableInterface
{
    use Description;
    use PageUrl;
    use Published;
    use Stamps;

    protected function requiredWiths() : array
    {
        return [
            $this->pageUrlPropertyName,
            $this->parsedDescriptionPropertyName,
            'category',
            'forumMember',
            'pictures',
        ];
    }

    public function url() : ?string
    {
        return $this->pageUrl();
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

    public function count() : int
    {
        return $this->pictures()->count();
    }

    public function latestPicture() : ?GalleryPicture
    {
        // sorted in reverse, so first
        return $this->pictures()->first();
    }

    public function displayPicture() : ?GalleryPicture
    {
        return $this->latestPicture();
    }
}
