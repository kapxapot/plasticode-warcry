<?php

namespace App\Hydrators;

use App\Models\NewsSource;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Basic\NewsSourceHydrator as BaseNewsSourceHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;

abstract class NewsSourceHydrator extends BaseNewsSourceHydrator
{
    protected GameRepositoryInterface $gameRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct(
            $userRepository,
            $cutParser,
            $linker,
            $parser
        );

        $this->gameRepository = $gameRepository;
    }

    /**
     * @param NewsSource $entity
     */
    public function hydrate(DbModel $entity) : NewsSource
    {
        /** @var NewsSource */
        $entity = parent::hydrate($entity);

        return $entity
            ->withGame(
                fn () => $this->gameRepository->get($entity->gameId)
            );
    }
}
