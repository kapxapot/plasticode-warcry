<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ComicIssue;
use App\Repositories\Interfaces\ComicPageRepositoryInterface;
use App\Repositories\Interfaces\ComicSeriesRepositoryInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;

class ComicIssueHydrator extends ParsingHydrator
{
    private ComicPageRepositoryInterface $comicPageRepository;
    private ComicSeriesRepositoryInterface $comicSeriesRepository;

    private LinkerInterface $linker;

    private TagsConfigInterface $tagsConfig;

    public function __construct(
        ComicPageRepositoryInterface $comicPageRepository,
        ComicSeriesRepositoryInterface $comicSeriesRepository,
        LinkerInterface $linker,
        TagsConfigInterface $tagsConfig
    )
    {
        $this->comicPageRepository = $comicPageRepository;
        $this->comicSeriesRepository = $comicSeriesRepository;

        $this->linker = $linker;

        $this->tagsConfig = $tagsConfig;
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
                fn () => $this->comicPageRepository->getAllByComic($entity)
            )
            ->withParsedDescription(
                fn () => $this->parse($entity->description)->text
            )
            ->withPageUrl(
                fn () => $this->linker->comicIssue($entity)
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
