<?php

namespace App\Models;

use Plasticode\Query;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\IO\Image;
use Plasticode\Models\AspectRatio;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Description;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;

class GalleryPicture extends DbModel
{
    use Description, FullPublish, Stamps, Tags;
    
    protected static $sortOrder = [
        ['field' => 'published_at', 'reverse' => true],
        ['field' => 'id', 'reverse' => true],
    ];

    // queries

    public static function getPublishedByAuthor($authorId) : Query
    {
        return self::getPublished()
            ->where('author_id', $authorId);
    }

    public static function getBasePublishedByAuthor($authorId) : Query
    {
        return self::getBasePublished()
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
        
        $this->save();
    }
    
    public function author() : GalleryAuthor
    {
        return GalleryAuthor::get($this->authorId);
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

        $this->save();
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
    
    public static function getBefore(GalleryPicture $borderPic, Query $baseQuery = null) : Query
    {
        $query = $baseQuery ?? self::getBasePublished();
        
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
                ->orderByDesc('id');
        }
        
        return $query;
    }
    
    public static function getAfter(GalleryPicture $borderPic, Query $baseQuery = null) : Query
    {
        $query = $baseQuery ?? self::getBasePublished();
        
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
                ->orderByAsc('id');
        }
        
        return $query;
    }
}
