<?php

namespace App\Services;

use Plasticode\Query;
use Plasticode\Collection;
use Plasticode\Util\Date;

use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;

class GalleryService
{
    private $pageSize;
    
    public function __construct(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }
    
    public function getAddedPicturesSliceByAuthor($game, \DateTime $start, \DateTime $end) : array
    {
        $slices = $this->getAddedPicturesSlice($game, $start, $end)
            ->group('author_id');
        
        $result = [];
        
        foreach ($slices as $authorId => $pictures) {
            $result[] = [
                'author' => GalleryAuthor::get($authorId),
                'pictures' => $pictures,
            ];
        }
        
        return $result;
    }
    
    public function getAddedPicturesSlice($game, \DateTime $start, \DateTime $end) : Collection
    {
        return GalleryPicture::getByGame($game)
            ->whereGt('published_at', Date::formatDb($start))
            ->whereLt('published_at', Date::formatDb($end))
            ->all();
    }
    
    public function getPage(Query $query, int $page = 1, int $pageSize = 0) : Query
    {
        if ($page <= 0) {
            $page = 1;
        }
        
        $limit = ($pageSize > 0) ? $pageSize : $this->pageSize;
        $offset = ($page - 1) * $limit;

        return $query->slice($offset, $limit);
    }
}
