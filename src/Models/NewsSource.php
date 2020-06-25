<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use App\Models\Traits\Stamps;
use Plasticode\Models\NewsSource as BaseNewsSource;

/**
 * @property integer|null $gameId
 * @property string $tags
 * @method static withGame(Game|callable|null $game)
 */
abstract class NewsSource extends BaseNewsSource implements NewsSourceInterface
{
    use Stamps;

    private string $gamePropertyName = 'game';

    protected function requiredWiths(): array
    {
        return [
            ...parent::requiredWiths(),
            $this->gamePropertyName,
        ];
    }

    // NewsSourceInterface

    public function game() : ?Game
    {
        return $this->getWithProperty(
            $this->gamePropertyName
        );
    }

    public function rootGame() : ?Game
    {
        return $this->game()
            ? $this->game()->root()
            : null;
    }

    public function creator() : ?User
    {
        return parent::creator();
    }
}
