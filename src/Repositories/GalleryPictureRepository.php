<?php

namespace App\Repositories;

use App\Collections\GalleryPictureCollection;
use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;
use App\Models\Game;
use App\Repositories\Interfaces\GalleryPictureRepositoryInterface;
use App\Repositories\Traits\ByGameRepository;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Idiorm\Traits\FullPublishedRepository;
use Plasticode\Util\SortStep;

class GalleryPictureRepository extends TaggedRepository implements GalleryPictureRepositoryInterface
{
    use ByGameRepository;
    use FullPublishedRepository;

    protected string $entityClass = GalleryPicture::class;

    /**
     * @return SortStep[]
     */
    protected function getSortOrder() : array
    {
        return [
            SortStep::createDesc('published_at'),
            SortStep::createDesc('id')
        ];
    }

    public function get(?int $id) : ?GalleryPicture
    {
        return $this->getEntity($id);
    }

    public function getAllByTag(
        string $tag,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this->byTagQuery(
                $this->query(),
                $tag,
                $limit
            )
        );
    }

    /**
     * Returns all published pictures that were published before given.
     */
    public function getAllBefore(GalleryPicture $pic) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this->getBeforeQuery($pic)
        );
    }

    /**
     * Returns all published pictures that were published after given.
     */
    public function getAllAfter(GalleryPicture $pic) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this->getBeforeQuery($pic)
        );
    }

    /**
     * Returns pictures in desc order.
     */
    protected function getBeforeQuery(
        GalleryPicture $pic,
        ?Query $query = null
    ) : Query
    {
        $query ??= $this->publishedQuery();

        return $query
            ->whereRaw(
                '(published_at < ? or (published_at = ? and id < ?))',
                [
                    $pic->publishedAt,
                    $pic->publishedAt,
                    $pic->getId(),
                ]
            )
            ->orderByDesc('published_at')
            ->thenByDesc('id');
    }

    /**
     * Returns pictures in asc order.
     */
    protected function getAfterQuery(
        GalleryPicture $pic,
        ?Query $query = null
    ) : Query
    {
        $query ??= $this->publishedQuery();

        return $query
            ->whereRaw(
                '(published_at > ? or (published_at = ? and id > ?))',
                [
                    $pic->publishedAt,
                    $pic->publishedAt,
                    $pic->getId(),
                ]
            )
            ->orderByAsc('published_at')
            ->thenByAsc('id');
    }

    /**
     * Returns all published pictures by author.
     */
    public function getAllByAuthor(
        GalleryAuthor $author,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this
                ->getPublishedByAuthorQuery($author)
                ->limit($limit)
        );
    }

    protected function getPublishedByAuthorQuery(GalleryAuthor $author) : Query
    {
        return $this->filterByAuthor(
            $this->publishedQuery(),
            $author
        );
    }

    protected function filterByAuthor(Query $query, GalleryAuthor $author) : Query
    {
        return $query->where('author_id', $author->getId());
    }

    /**
     * Returns all published pictures by game.
     */
    public function getAllByGame(
        ?Game $game = null,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this
                ->getPublishedByGameQuery($game)
                ->limit($limit)
        );
    }

    protected function getPublishedByGameQuery(?Game $game = null) : Query
    {
        return $this->filterByGame(
            $this->publishedQuery(),
            $game
        );
    }

    /**
     * Returns the previous picture of the same author.
     */
    public function getPrevSibling(GalleryPicture $pic) : ?GalleryPicture
    {
        return $this
            ->filterByAuthor(
                $this->getBeforeQuery($pic),
                $pic->author()
            )
            ->one();
    }

    /**
     * Returns the next picture of the same author.
     */
    public function getNextSibling(GalleryPicture $pic) : ?GalleryPicture
    {
        return $this
            ->filterByAuthor(
                $this->getAfterQuery($pic),
                $pic->author()
            )
            ->one();
    }
}
