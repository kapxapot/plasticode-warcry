<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\ParsingContext;

/**
 * @property integer $published
 * @property string|null $publishedAt
 * @property integer|null $createdBy
 * @property string $createdAt
 * @property integer|null $updatedBy
 * @property string $updatedAt
 * @property string $tags
 */
abstract class NewsSource extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use FullPublished;
    use Stamps;
    use Tags;

    private ?ParserInterface $parser = null;

    public function withParser(ParserInterface $parser) : self
    {
        $this->parser = $parser;
        return $this;
    }

    public function game() : ?Game
    {
        return $this->gameId
            ? Game::get($this->gameId)
            : null;
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

    public abstract function parsedDescription(ParserInterface $parser) : ?ParsingContext;

    public abstract static function search(string $searchQuery) : Collection;
    
    public abstract function code() : string;
    
    // NewsSourceInterface

    public abstract function url() : ?string;
    
    public abstract static function getNewsByTag(string $tag) : Query;

    public abstract static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query;
    
    public abstract static function getNewsBefore(Game $game, string $date) : Query;
    
    public abstract static function getNewsAfter(Game $game, string $date) : Query;
    
    public abstract static function getNewsByYear(int $year) : Query;

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
