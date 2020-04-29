<?php

namespace App\Collections;

use App\Models\Game;
use Plasticode\TypedCollection;

class GameCollection extends TypedCollection
{
    protected string $class = Game::class;

    public function newsForums() : ForumCollection
    {
        return ForumCollection::from(
            $this
                ->map(
                    fn (Game $g) => $g->newsForum()
                )
                ->clean()
        );
    }
}
