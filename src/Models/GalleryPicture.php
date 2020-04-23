<?php

namespace App\Models;

use Plasticode\AspectRatio;
use Plasticode\IO\Image;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Webmozart\Assert\Assert;

/**
 * @property integer $id
 * @property integer $authorId
 * @property integer|null $gameId
 * @property string|null $comment
 * @property integer|null $width
 * @property integer|null $height
 * @property string|null $avgColor
 * @property string|null $tags
 * @property string $pictureType
 * @property integer $published
 * @property string|null $publishedAt
 * @method GalleryAuthor author()
 * @method string ext()
 * @method Game|null game()
 * @method static|null next()
 * @method string pageUrl()
 * @method static|null prev()
 * @method string thumbUrl()
 * @method string url()
 * @method self withAuthor(GalleryAuthor|callable $author)
 * @method self withExt(string|callable $ext)
 * @method self withGame(Game|callable|null $game)
 * @method self withNext(static|callable|null $next)
 * @method self withPageUrl(string|callable $pageUrl)
 * @method self withPrev(static|callable|null $prev)
 * @method self withThumbUrl(string|callable $thumbUrl)
 * @method self withUrl(string|callable $url)
 */
class GalleryPicture extends DbModel
{
    use Description;
    use FullPublished;
    use Stamps;
    use Tags;

    private const DEFAULT_BG_COLOR = '255,255,255,1';

    protected function requiredWiths(): array
    {
        return [
            'author',
            'ext',
            'game',
            //'next',
            'pageUrl',
            //'prev',
            'thumbUrl',
            'url'
        ];
    }

    public function withWidth(int $width) : self
    {
        $this->width = $width;
        return $this;
    }

    public function withHeight(int $height) : self
    {
        $this->height = $height;
        return $this;
    }

    public function withAvgColor(string $avgColor) : self
    {
        $this->avgColor = $avgColor;
        return $this;
    }

    public function ratioCss() : string
    {
        Assert::greaterThan($this->width, 0);
        Assert::greaterThan($this->height, 0);

        $ratio = new AspectRatio($this->width, $this->height);

        return $ratio->cssClasses();
    }

    public function bgColor() : array
    {
        $bgColor = $this->avgColor ?? self::DEFAULT_BG_COLOR;

        return Image::deserializeRGBA($bgColor);
    }
}
