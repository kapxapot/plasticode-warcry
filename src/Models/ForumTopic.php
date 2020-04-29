<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Tagged;
use Plasticode\Util\Arrays;
use Plasticode\Util\Date;

/**
 * @property integer $forumId
 * @property integer|null $startDate
 * @property integer $starterId
 * @property string|null $starterName
 * @property string $title
 * @method Forum forum()
 * @method string forumUrl()
 * @method string|null parsedPost()
 * @method static withForum(Forum|callable $forum)
 * @method static withForumUrl(string|callable $forumUrl)
 * @method static withFullText(string|callable|null $fullText)
 * @method static withGame(Game|callable|null $game)
 * @method static withParsedPost(string|callable|null $parsedPost)
 * @method static withUrl(string|callable|null $url)
 * @method static withShortText(string|callable|null $shortText)
 */
class ForumTopic extends DbModel implements NewsSourceInterface
{
    use Tagged;

    private const TIME_FORMAT = '%Y-%m-%d %H:%M:%S';

    protected static string $idField = 'tid';

    private string $fullTextPropertyName = 'fullText';
    private string $gamePropertyName = 'game';
    private string $shortTextPropertyName = 'shortText';
    private string $urlPropertyName = 'url';

    protected function requiredWiths(): array
    {
        return [
            $this->gamePropertyName,
            $this->urlPropertyName,
            'forum',
            'forumUrl',
        ];
    }

    /**
     * Tagged trait function override.
     * 
     * @return string[]
     */
    public function getTags() : array
    {
        $tags = $this
            ->tags()
            ->extract('tag_text')
            ->toArray();

        return Arrays::trim($tags);
    }

    public function isNews() : bool
    {
        return $this->forum()->isNewsForum();
    }

    public function forumPost() : ForumPost
    {
        return ForumPost::getByForumTopic($this->getId());
    }

    public function post() : ?string
    {
        return $this->forumPost()
            ? $this->forumPost()->post
            : null;
    }

    public function tags() : Collection
    {
        return ForumTag::getByForumTopic($this->getId())->all();
    }

    public function published() : int
    {
        return 1;
    }

    public function publishedAt() : string
    {
        return strftime(self::TIME_FORMAT, $this->startDate);
    }

    public function publishedAtIso() : string
    {
        return Date::iso($this->publishedAt());
    }

    public function creator() : array
    {
        return [
            'forum_member' => ForumMember::get($this->starterId),
            'display_name' => $this->starterName,
        ];
    }

    public function updater() : array
    {
        return $this->creator();
    }

    public function createdAtIso() : string
    {
        return $this->publishedAtIso();
    }

    public function updatedAtIso() : string
    {
        return $this->createdAtIso();
    }

    // LinkableInterface

    public function url() : ?string
    {
        return $this->getWithProperty(
            $this->urlPropertyName
        );
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
        return null;
    }

    public function image() : ?string
    {
        return null;
    }

    public function video() : ?string
    {
        return null;
    }

    public function displayTitle() : string
    {
        return self::$container->newsParser->decodeTopicTitle($this->title);
    }

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
}
