<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicSeries;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use App\Repositories\Interfaces\ComicPublisherRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ComicSeriesHydrator extends Hydrator
{
    private ComicIssueRepositoryInterface $comicIssueRepository;
    private ComicPublisherRepositoryInterface $comicPublisherRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;

    public function __construct(
        ComicIssueRepositoryInterface $comicIssueRepository,
        ComicPublisherRepositoryInterface $comicPublisherRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker
    )
    {
        $this->comicIssueRepository = $comicIssueRepository;
        $this->comicPublisherRepository = $comicPublisherRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;
    }

    /**
     * @param ComicSeries $entity
     */
    public function hydrate(DbModel $entity) : ComicSeries
    {
        return $entity
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            )
            ->withIssues(
                fn () => $this->comicIssueRepository->getAllBySeries($entity)
            )
            ->withPublisher(
                fn () => $this->comicPublisherRepository->get($entity->publisherId)
            )
            ->withPageUrl(
                fn () => $this->linker->comicSeries($entity)
            );
    }
}
