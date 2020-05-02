<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\News;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class NewsHydrator extends NewsSourceHydrator
{
    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser,
        TagsConfigInterface $tagsConfig
    )
    {
        parent::__construct(
            $gameRepository,
            $userRepository,
            $cutParser,
            $linker,
            $parser,
            $tagsConfig
        );
    }

    /**
     * @param News $entity
     */
    public function hydrate(DbModel $entity) : News
    {
        /** @var News */
        $entity = parent::hydrate($entity);

        return $entity
            ->withUrl(
                fn () => $this->linker->news($entity->getId())
            );
    }
}
