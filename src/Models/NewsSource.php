<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tagged;
use Plasticode\Parsing\ParsingContext;

/**
 * @property integer|null $gameId
 * @property string $tags
 * @method Game|null game()
 * @method ParsingContext|null parsed
 * @method static withGame(Game|callable|null $game)
 * @method static withParsed(ParsingContext|callable|null $parsed)
 */
abstract class NewsSource extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use FullPublished;
    use Stamps;
    use Tagged;

    protected function requiredWiths(): array
    {
        return ['game', 'parsed'];
    }

    public function rootGame() : ?Game
    {
        return $this->game()
            ? $this->game()->root()
            : null;
    }

    public function largeImage() : ?string
    {
        return $this->parsed()
            ? $this->parsed()->largeImage
            : null;
    }
    
    public function image() : ?string
    {
        return $this->parsed()
            ? $this->parsed()->image
            : null;
    }

    public function video() : ?string
    {
        return $this->parsed()
            ? $this->parsed()->video
            : null;
    }

    public function parsed() : ?ParsingContext
    {
        return $this->parsedDescription($this->parser);
    }

    public function parsedText() : ?string
    {
        return $this->parsed()
            ? $this->parsed()->text
            : null;
    }

    // public abstract static function search(string $searchQuery) : Collection;

    public abstract function code() : string;

    // NewsSourceInterface

    public abstract function url() : ?string;

    // public abstract static function getNewsByTag(string $tag) : Query;

    // public abstract static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query;

    // public abstract static function getNewsBefore(Game $game, string $date) : Query;

    // public abstract static function getNewsAfter(Game $game, string $date) : Query;

    // public abstract static function getNewsByYear(int $year) : Query;

    public abstract function displayTitle() : string;

    public function fullText() : ?string
    {
        return $this->lazy(
            function () {
                $cutParser = self::$container->cutParser;
                $text = $this->parsedText();
                
                return $cutParser->full($text);
            }
        );
    }

    public function shortText() : ?string
    {
        return $this->lazy(
            function () {
                $cutParser = self::$container->cutParser;
                $text = $this->parsedText();
                
                return $cutParser->short($text);
            }
        );
    }
}
