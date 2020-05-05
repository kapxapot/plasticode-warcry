<?php

namespace App\Models;

use App\Collections\GalleryPictureCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\Published;
use Plasticode\Models\Traits\Stamps;

/**
 * @property string|null $alias
 * @property string|null $artStation
 * @property integer $categoryId
 * @property string|null $description
 * @property string|null $deviant
 * @property string $name
 * @property string|null $realName
 * @property string|null $realNameEn
 * @method GalleryAuthorCategory category()
 * @method ForumMember|null forumMember()
 * @method string pageUrl()
 * @method string|null parsedDescription()
 * @method GalleryPictureCollection pictures()
 * @method static withCategory(GalleryAuthorCategory|callable $category)
 * @method static withForumMember(ForumMember|callable|null $forumMember)
 * @method static withPageUrl(string|callable $pageUrl)
 * @method static withParsedDescription(string|callable|null $parsedDescription)
 * @method static withPictures(GalleryPictureCollection|callable $pictures)
 */
class GalleryAuthor extends DbModel implements LinkableInterface
{
    use Published;
    use Stamps;

    protected function requiredWiths(): array
    {
        return [
            'category',
            'forumMember',
            'pageUrl',
            'parsedDescription',
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
