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
use Plasticode\Util\Date;
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
            SortStep::desc('published_at'),
            SortStep::desc('id')
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
            $this
                ->query()
                ->apply(
                    fn (Query $q) => $this->filterByTag($q, $tag, $limit)
                )
        );
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
                ->publishedQuery()
                ->apply(
                    fn (Query $q) => $this->filterByAuthor($q, $author)
                )
                ->limit($limit)
        );
    }

    /**
     * Returns all published pictures by game.
     */
    public function getAllByGame(
        ?Game $game,
        int $limit = 0
    ) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this
                ->byGameQuery($game)
                ->limit($limit)
        );
    }

    /**
     * Returns all published pictures that were published before given.
     */
    public function getAllBefore(GalleryPicture $pic) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this->beforeQuery($pic)
        );
    }

    /**
     * Returns all published pictures that were published after given.
     */
    public function getAllAfter(GalleryPicture $pic) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this->afterQuery($pic)
        );
    }

    /**
     * Returns the previous picture of the same author.
     */
    public function getPrevSibling(GalleryPicture $pic) : ?GalleryPicture
    {
        return $this
            ->beforeQuery($pic)
            ->apply(
                fn (Query $q) => $this->filterByAuthor($q, $pic->author())
            )
            ->one();
    }

    /**
     * Returns the next picture of the same author.
     */
    public function getNextSibling(GalleryPicture $pic) : ?GalleryPicture
    {
        return $this
            ->afterQuery($pic)
            ->apply(
                fn (Query $q) => $this->filterByAuthor($q, $pic->author())
            )
            ->one();
    }

    public function getAddedPicturesSlice(
        ?Game $game,
        \DateTime $start,
        \DateTime $end
    ) : GalleryPictureCollection
    {
        return GalleryPictureCollection::from(
            $this
                ->byGameQuery($game)
                ->whereGt('published_at', Date::formatDb($start))
                ->whereLt('published_at', Date::formatDb($end))
        );
    }

    // queries

    protected function beforeQuery(GalleryPicture $pic) : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterBefore($q, $pic)
            );
    }

    protected function afterQuery(GalleryPicture $pic) : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterAfter($q, $pic)
            );
    }

    protected function byGameQuery(?Game $game) : Query
    {
        return $this
            ->publishedQuery()
            ->apply(
                fn (Query $q) => $this->filterByGameTree($q, $game)
            );
    }

    // filters

    /**
     * Returns pictures in desc order.
     */
    protected function filterBefore(Query $query, GalleryPicture $pic) : Query
    {
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
    protected function filterAfter(Query $query, GalleryPicture $pic) : Query
    {
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

    protected function filterByAuthor(Query $query, GalleryAuthor $author) : Query
    {
        return $query->where('author_id', $author->getId());
    }
}
