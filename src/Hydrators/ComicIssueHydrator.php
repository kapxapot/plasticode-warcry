<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicIssue;
use App\Repositories\Interfaces\ComicIssuePageRepositoryInterface;
use App\Repositories\Interfaces\ComicSeriesRepositoryInterface;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;

class ComicIssueHydrator extends ParsingHydrator
{
    private ComicIssuePageRepositoryInterface $comicIssuePageRepository;
    private ComicSeriesRepositoryInterface $comicSeriesRepository;

    private LinkerInterface $linker;

    public function __construct(
        ComicIssuePageRepositoryInterface $comicIssuePageRepository,
        ComicSeriesRepositoryInterface $comicSeriesRepository,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct($parser);

        $this->comicIssuePageRepository = $comicIssuePageRepository;
        $this->comicSeriesRepository = $comicSeriesRepository;

        $this->linker = $linker;
    }

    /**
     * @param ComicIssue $entity
     */
    public function hydrate(DbModel $entity) : ComicIssue
    {
        return $entity
            ->withSeries(
                fn () => $this->comicSeriesRepository->get($entity->seriesId)
            )
            ->withPages(
                fn () => $this->comicIssuePageRepository->getAllByComic($entity)
            )
            ->withParsedDescription(
                fn () => $this->parse($entity->description)->text
            )
            ->withPageUrl(
                fn () => $this->linker->comicIssue($entity)
            )
            ->withTagLinks(
                fn () => $this->linker->tagLinks($entity)
            );
    }
}
