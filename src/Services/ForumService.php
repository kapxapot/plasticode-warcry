<?php

namespace App\Services;

use App\Collections\ForumCollection;
use App\Models\Forum;
use App\Models\Game;
use App\Repositories\Interfaces\GameRepositoryInterface;

class ForumService
{
    private GameRepositoryInterface $gameRepository;

    public function __construct(
        GameRepositoryInterface $gameRepository
    )
    {
        $this->gameRepository = $gameRepository;
    }

    public function getNewsForums(?Game $game = null) : ForumCollection
    {
        return $this
            ->gameRepository
            ->getSubTreeOrAll($game)
            ->newsForums();
    }

    public function isNewsForum(Forum $forum) : bool
    {
        return $this
            ->getNewsForums()
            ->any(
                fn (Forum $f) => $f->equals($forum)
            );
    }
}
