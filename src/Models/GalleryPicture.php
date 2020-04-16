<?php

namespace App\Models;

use App\Core\Interfaces\LinkerInterface;
use Plasticode\AspectRatio;
use Plasticode\Query;
use Plasticode\IO\Image;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\SortStep;
use Webmozart\Assert\Assert;

/**
 * @property integer $id
 * @property integer $authorId
 * @property integer $gameId
 * @property string $comment
 * @property integer|null $width
 * @property integer|null $height
 * @property string|null $avgColor
 * @property string|null $tags
 * @property integer $published
 * @property string $publishedAt
 * @property LinkerInterface $linker
 */
class GalleryPicture extends DbModel
{
    use Description;
    use FullPublished;
    use Stamps;
    use Tags;

    private const DEFAULT_BG_COLOR = '255,255,255,1';

    private ?GalleryAuthor $author = null;

    public function withAuthor(GalleryAuthor $author) : self
    {
        $this->author = $author;
        return $this;
    }
    
    public function author() : GalleryAuthor
    {
        return
            $this->author
            ??
            GalleryAuthor::get($this->authorId);
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
    
    /**
     * @return SortStep[]
     */
    protected static function getSortOrder() : array
    {
        return [
            SortStep::createDesc('published_at'),
            SortStep::createDesc('id')
        ];
    }

    // queries

    public static function getPublishedByAuthor($authorId) : Query
    {
        return self::getPublished()
            ->where('author_id', $authorId);
    }

    public static function getByGame(Game $game = null) : Query
    {
        $query = self::getPublished();

        if ($game) {
            $query = $game->filter($query);
        }

        return $query;
    }

    public static function getLatestByGame(Game $game = null, int $limit = null) : Query
    {
        return self::getByGame($game)
            ->limit($limit);
    }

    // PROPS

    public function game() : ?Game
    {
        return $this->gameId
            ? Game::get($this->gameId)
            : null;
    }
    
    public function ext() : ?string
    {
        return self::$container->linker->getExtension($this->pictureType);
    }

    public function url() : string
    {
        return self::$container->linker->galleryPictureImg($this);
    }
    
    public function thumbUrl() : string
    {
        $this->failIfNotPersisted();

        //self::$container->gallery->ensureThumbExists($this);

        return self::$container->linker->galleryThumbImg($this);
    }
    
    public function ratioCss() : string
    {
        $this->failIfNotPersisted();

        Assert::greaterThan($this->width, 0);
        Assert::greaterThan($this->height, 0);
        
        $ratio = new AspectRatio($this->width, $this->height);
        
        return $ratio->cssClasses();
    }
    
    public function bgColor() : array
    {
        $this->failIfNotPersisted();

        $bgColor = $this->avgColor ?? self::DEFAULT_BG_COLOR;
        
        return Image::deserializeRGBA($bgColor);
    }
    
    public function pageUrl() : string
    {
        return self::$container->linker->galleryPicture(
            $this->author()->alias, $this->getId()
        );
    }

    private function getSiblingsBefore() : Query
    {
        return self::getBefore($this)
            ->where('author_id', $this->authorId);
    }

    private function getSiblingsAfter() : Query
    {
        return self::getAfter($this)
            ->where('author_id', $this->authorId);
    }

    /**
     * Reversed
     */
    public function prev() : ?self
    {
        return $this->lazy(
            function () {
                return $this->getSiblingsAfter()->one();
            }
        );
    }
    
    /**
     * Reversed
     */
    public function next() : ?self
    {
        return $this->lazy(
            function () {
                return $this->getSiblingsBefore()->one();
            }
        );
    }
    
    public static function getBefore(GalleryPicture $borderPic, Query $query = null) : Query
    {
        $query = $query ?? self::getPublished();
        
        if ($borderPic) {
            $query = $query
                ->whereRaw(
                    '(published_at < ? or (published_at = ? and id < ?))',
                    [
                        $borderPic->publishedAt,
                        $borderPic->publishedAt,
                        $borderPic->getId(),
                    ]
                )
                ->orderByDesc('published_at')
                ->thenByDesc('id');
        }
        
        return $query;
    }
    
    public static function getAfter(GalleryPicture $borderPic, Query $query = null) : Query
    {
        $query = $query ?? self::getPublished();
        
        if ($borderPic) {
            $query = $query
                ->whereRaw(
                    '(published_at > ? or (published_at = ? and id > ?))',
                    [
                        $borderPic->publishedAt,
                        $borderPic->publishedAt,
                        $borderPic->getId(),
                    ]
                )
                ->orderByAsc('published_at')
                ->thenByAsc('id');
        }
        
        return $query;
    }
}
