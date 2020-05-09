<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\NewsSource;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Hydrators\Basic\ParsingHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;

abstract class NewsSourceHydrator extends ParsingHydrator
{
    protected GameRepositoryInterface $gameRepository;
    protected UserRepositoryInterface $userRepository;

    protected CutParser $cutParser;
    protected LinkerInterface $linker;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct($parser);

        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;

        $this->cutParser = $cutParser;
        $this->linker = $linker;
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
                    fn () => $this->parse($entity->rawText())
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
                fn () => $this->linker->tagLinks($entity)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }
}
