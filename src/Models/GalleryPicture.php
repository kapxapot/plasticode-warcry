<?php

namespace App\Models;

use App\Models\Traits\Description;
use App\Models\Traits\PageUrl;
use App\Models\Traits\Stamps;
use Plasticode\AspectRatio;
use Plasticode\IO\Image;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\Tagged;
use Webmozart\Assert\Assert;

/**
 * @property integer $authorId
 * @property string|null $avgColor
 * @property string|null $comment
 * @property integer|null $gameId
 * @property integer|null $height
 * @property integer $official
 * @property string $pictureType
 * @property string|null $points
 * @property string|null $tags
 * @property string $thumbType
 * @property integer|null $width
 * @method GalleryAuthor author()
 * @method string ext()
 * @method Game|null game()
 * @method static|null next()
 * @method static|null prev()
 * @method string thumbUrl()
 * @method static withAuthor(GalleryAuthor|callable $author)
 * @method static withExt(string|callable $ext)
 * @method static withGame(Game|callable|null $game)
 * @method static withNext(static|callable|null $next)
 * @method static withPrev(static|callable|null $prev)
 * @method static withThumbUrl(string|callable $thumbUrl)
 */
class GalleryPicture extends DbModel implements LinkableInterface
{
    use Description;
    use FullPublished;
    use Linkable;
    use PageUrl;
    use Stamps;
    use Tagged;

    private const DEFAULT_BG_COLOR = '255,255,255,1';

    protected function requiredWiths(): array
    {
        return [
            $this->pageUrlPropertyName,
            $this->parsedDescriptionPropertyName,
            $this->urlPropertyName,
            'author',
            'ext',
            'game',
            'next',
            'prev',
            'thumbUrl',
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

    public function isOfficial() : bool
    {
        return self::toBool($this->official);
    }
}
