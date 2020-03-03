<?php

namespace App\Models;

use App\Core\Interfaces\LinkerInterface;
use Plasticode\AspectRatio;
use Plasticode\Query;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\IO\Image;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\SortStep;

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
    use Description, FullPublish, Stamps, Tags;

    /** @var GalleryAuthor|null */
    private $author;

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
        return self::$linker->getExtension($this->pictureType);
    }

    public function url() : string
    {
        return self::$linker->galleryPictureImg($this);
    }
    
    public function thumbUrl() : string
    {
        $this->failIfNotPersisted();

        self::$container->gallery->ensureThumbExists($this);

        return self::$linker->galleryThumbImg($this);
    }
    
    public function ratioCss() : string
    {
        $this->failIfNotPersisted();
        
        try {
            // ensure picture has width / height
            $this->ensureWidthHeightSet();
            
            $ratio = new AspectRatio($this->width, $this->height);
            
            return $ratio->cssClasses();
        }
        catch (\Exception $ex) {
            return '';
        }
    }
    
    private function ensureWidthHeightSet()
    {
        if ($this->width > 0 && $this->height > 0) {
            return;
        }
        
        $picture = self::$container->gallery->loadPicture($this);
        
        if (!$picture || !($picture->width > 0) || !($picture->height > 0)) {
            throw new InvalidResultException(
                'Invalid image file for gallery picture ' . $this->toString() . '.'
            );
        }
        
        $this->width = $picture->width;
        $this->height = $picture->height;

        // Todo: temporary dirty trick
        self::save($this);
    }
    
    public function bgColor() : array
    {
        $this->failIfNotPersisted();
        
        try {
            $this->ensureAvgColorSet();
        
            return Image::deserializeRGBA($this->avgColor);
        }
        catch (\Exception $ex) {
            return Image::deserializeRGBA('255,255,255,1');
        }
    }
    
    private function ensureAvgColorSet()
    {
        if ($this->avgColor !== null) {
            return;
        }
        
        $this->avgColor = self::$container->gallery->getAvgColor($this);

        // Todo: temporary dirty trick
        self::save($this);
    }
    
    public function pageUrl() : string
    {
        return self::$linker->galleryPicture(
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
