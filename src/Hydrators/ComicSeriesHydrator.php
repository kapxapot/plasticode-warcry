<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicSeries;
use App\Repositories\Interfaces\ComicIssueRepositoryInterface;
use App\Repositories\Interfaces\ComicPublisherRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class ComicSeriesHydrator extends ParsingHydrator
{
    private ComicIssueRepositoryInterface $comicIssueRepository;
    private ComicPublisherRepositoryInterface $comicPublisherRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;

    private TagsConfigInterface $tagsConfig;

    public function __construct(
        ComicIssueRepositoryInterface $comicIssueRepository,
        ComicPublisherRepositoryInterface $comicPublisherRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker,
        ParserInterface $parser,
        TagsConfigInterface $tagsConfig
    )
    {
        parent::__construct($parser);

        $this->comicIssueRepository = $comicIssueRepository;
        $this->comicPublisherRepository = $comicPublisherRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;

        $this->tagsConfig = $tagsConfig;
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
            ->withParsedDescription(
                fn () => $this->parse($entity->description)->text
            )
            ->withPageUrl(
                fn () => $this->linker->comicSeries($entity)
            )
            ->withTagLinks(
                fn () =>
                $this->linker->tagLinks(
                    $entity,
                    $this->tagsConfig->getTab(get_class($entity))
                )
            );
    }
}
