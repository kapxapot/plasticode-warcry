<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\Video;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;

class VideoHydrator extends NewsSourceHydrator
{
    public function __construct(
        GameRepositoryInterface $gameRepository,
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct(
            $gameRepository,
            $userRepository,
            $cutParser,
            $linker,
            $parser
        );
    }

    /**
     * @param Video $entity
     */
    public function hydrate(DbModel $entity) : Video
    {
        /** @var Video */
        $entity = parent::hydrate($entity);

        return $entity
            ->withVideo(
                fn () => $this->linker->youtube($entity->youtubeCode)
            )
            ->withUrl(
                fn () => $this->linker->video($entity->getId())
            );
    }
}
