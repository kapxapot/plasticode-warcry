<?php

namespace App\Models\Traits;

use App\Models\ComicPublisher;
use App\Models\Game;

/**
 * Common base for comic series & comic standalones.
 * 
 * @property string $alias
 * @property integer $gameId
 * @property string $nameEn
 * @property string|null $nameRu
 * @property integer $publisherId
 * @property string|null $tags
 * @method Game game()
 * @method ComicPublisher publisher()
 * @method static withGame(Game|callable $game)
 * @method static withPublisher(ComicPublisher|callable $publisher)
 */
trait ComicRoot
{
    use Description;
    use PageUrl;

    protected string $gamePropertyName = 'game';
    protected string $publisherPropertyName = 'publisher';

    public function name() : string
    {
        return $this->nameRu ?? $this->nameEn;
    }

    public function subName() : ?string
    {
        return ($this->nameRu && $this->nameRu != $this->nameEn)
            ? $this->nameEn
            : null;
    }

    public function fullName() : string
    {
        $name = $this->name();

        if ($this->subName()) {
            $name .= ' (' . $this->subName() . ')';
        }

        return $name;
    }
}
