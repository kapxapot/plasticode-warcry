<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Linkable;
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
 */
abstract class NewsSource extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use FullPublished;
    use Linkable;
    use Stamps;
    use Tagged;

    private string $gamePropertyName = 'game';
    private string $fullTextPropertyName = 'fullText';
    private string $shortTextPropertyName = 'shortText';

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
            $this->fullTextPropertyName,
            $this->gamePropertyName,
            $this->shortTextPropertyName,
            $this->tagLinksPropertyName,
            $this->updaterPropertyName,
            $this->urlPropertyName,
            'parsed',
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
    // implemented in Linkable trait

    // TaggedInterface
    // implemented in Tagged trait

    // SearchableInterface

    abstract public function code() : string;
}
