<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tagged;
use Plasticode\Parsing\ParsingContext;

/**
 * @property integer|null $gameId
 * @property string $tags
 * @method ParsingContext|null parsed()
 * @method TagLinkCollection tagLinks()
 * @method static withFullText(string|callable|null $fullText)
 * @method static withGame(Game|callable|null $game)
 * @method static withParsed(ParsingContext|callable|null $parsed)
 * @method static withShortText(string|callable|null $shortText)
 * @method static withTagLinks(TagLinkCollection|callable $tagLinks)
 * @method static withUrl(string|callable|null $url)
 */
abstract class NewsSource extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use FullPublished;
    use Stamps;
    use Tagged;

    private string $gamePropertyName = 'game';
    private string $fullTextPropertyName = 'fullText';
    private string $shortTextPropertyName = 'shortText';
    private string $urlPropertyName = 'url';

    protected function requiredWiths(): array
    {
        return [
            $this->gamePropertyName,
            'parsed',
            $this->fullTextPropertyName,
            $this->shortTextPropertyName,
            $this->tagLinksPropertyName,
            $this->urlPropertyName,
            $this->creatorPropertyName,
            $this->updaterPropertyName,
        ];
    }

    public function parsedText() : ?string
    {
        return $this->parsed()
            ? $this->parsed()->text
            : null;
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

    abstract public function displayTitle() : string;

    abstract public function rawText() : ?string;

    public function fullText() : ?string
    {
        return $this->getWithProperty(
            $this->fullTextPropertyName
        );
    }

    public function shortText() : ?string
    {
        return $this->getWithProperty(
            $this->shortTextPropertyName
        );
    }

    // LinkableInterface

    public function url() : ?string
    {
        return $this->getWithProperty(
            $this->urlPropertyName
        );
    }

    // TaggedInterface
    // implemented in Tagged trait

    // SearchableInterface

    abstract public function code() : string;
}
