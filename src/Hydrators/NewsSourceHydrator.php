<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\NewsSource;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Collections\TagLinkCollection;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Parsing\ParsingContext;

abstract class NewsSourceHydrator extends Hydrator
{
    protected GameRepositoryInterface $gameRepository;
    protected UserRepositoryInterface $userRepository;

    protected CutParser $cutParser;
    protected LinkerInterface $linker;
    protected ParserInterface $parser;

    protected TagsConfigInterface $tagsConfig;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser,
        TagsConfigInterface $tagsConfig
    )
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;

        $this->cutParser = $cutParser;
        $this->linker = $linker;
        $this->parser = $parser;

        $this->tagsConfig = $tagsConfig;
    }

    /**
     * @param NewsSource $entity
     */
    public function hydrate(DbModel $entity) : NewsSource
    {
        return $entity
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            )
            ->withParsed(
                $this->frozen(
                    fn () => $this->parseText($entity)
                )
            )
            ->withFullText(
                $this->frozen(
                    fn () =>
                    $this->cutParser->full(
                        $entity->parsedText()
                    )
                )
            )
            ->withShortText(
                $this->frozen(
                    fn () =>
                    $this->cutParser->short(
                        $entity->parsedText()
                    )
                )
            )
            ->withTagLinks(
                fn () =>
                $this->linker->tagLinks(
                    $entity,
                    $this->tagsConfig->getTab(get_class($entity))
                )
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }

    private function parseText(NewsSource $entity) : ?ParsingContext
    {
        $text = $entity->rawText();

        if (strlen($text) == 0) {
            return null;
        }

        $context = $this->parser->parse($text);
        $context = $this->parser->renderLinks($context);

        return $context;
    }
}
