<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicStandalone;
use App\Repositories\Interfaces\ComicPublisherRepositoryInterface;
use App\Repositories\Interfaces\ComicStandalonePageRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class ComicStandaloneHydrator extends ParsingHydrator
{
    private ComicPublisherRepositoryInterface $comicPublisherRepository;
    private ComicStandalonePageRepositoryInterface $comicStandalonePageRepository;
    private GameRepositoryInterface $gameRepository;

    private LinkerInterface $linker;

    public function __construct(
        ComicPublisherRepositoryInterface $comicPublisherRepository,
        ComicStandalonePageRepositoryInterface $comicStandalonePageRepository,
        GameRepositoryInterface $gameRepository,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct($parser);

        $this->comicPublisherRepository = $comicPublisherRepository;
        $this->comicStandalonePageRepository = $comicStandalonePageRepository;
        $this->gameRepository = $gameRepository;

        $this->linker = $linker;
    }

    /**
     * @param ComicStandalone $entity
     */
    public function hydrate(DbModel $entity) : ComicStandalone
    {
        return $entity
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            )
            ->withPublisher(
                fn () => $this->comicPublisherRepository->get($entity->publisherId)
            )
            ->withPages(
                fn () => $this->comicStandalonePageRepository->getAllByComic($entity)
            )
            ->withParsedDescription(
                fn () => $this->parse($entity->description)->text
            )
            ->withPageUrl(
                fn () => $this->linker->comicStandalone($entity)
            );
    }
}
