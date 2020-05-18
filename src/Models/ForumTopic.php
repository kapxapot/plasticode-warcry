<?php

namespace App\Models;

use App\Collections\ForumTagCollection;
use App\Models\Interfaces\NewsSourceInterface;
use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\Linkable;
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
 * @method ForumPost|null forumPost()
 * @method string forumUrl()
 * @method string|null parsedPost()
 * @method ForumMember|null starterForumMember()
 * @method ForumTagCollection tags()
 * @method static withDisplayTitle(string|callable $displayTitle)
 * @method static withForum(Forum|callable $forum)
 * @method static withForumPost(ForumPost|callable|null $forumPost)
 * @method static withForumUrl(string|callable $forumUrl)
 * @method static withFullText(string|callable|null $fullText)
 * @method static withGame(Game|callable|null $game)
 * @method static withParsedPost(string|callable|null $parsedPost)
 * @method static withShortText(string|callable|null $shortText)
 * @method static withStarterForumMember(ForumMember|callable|null $forumMember)
 * @method static withTags(ForumTagCollection|callable $tags)
 */
class ForumTopic extends DbModel implements NewsSourceInterface
{
    use Linkable;
    use Tagged;

    private const TIME_FORMAT = '%Y-%m-%d %H:%M:%S';

    protected static string $idField = 'tid';

    private string $displayTitlePropertyName = 'displayTitle';
    private string $fullTextPropertyName = 'fullText';
    private string $gamePropertyName = 'game';
    private string $shortTextPropertyName = 'shortText';

    protected function requiredWiths(): array
    {
        return [
            $this->displayTitlePropertyName,
            $this->fullTextPropertyName,
            $this->gamePropertyName,
            $this->shortTextPropertyName,
            $this->tagLinksPropertyName,
            $this->urlPropertyName,
            'forum',
            'forumPost',
            'forumUrl',
            'parsedPost',
            'starterForumMember',
            'tags',
        ];
    }

    /**
     * Tagged trait override.
     * 
     * @return string[]
     */
    public function getTags() : array
    {
        $tags = $this
            ->tags()
            ->tagTexts()
            ->toArray();

        return Arrays::trim($tags);
    }

    public function isNews() : bool
    {
        return $this->forum()->isNewsForum();
    }

    public function post() : ?string
    {
        return $this->forumPost()
            ? $this->forumPost()->post
            : null;
    }

    public function hasText() : bool
    {
        return strlen($this->post()) > 0;
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

    public function creator() : ?User
    {
        /** @var User */
        $user = User::create(['name' => $this->starterName]);

        return $user->withForumMember(
            $this->starterForumMember()
        );
    }

    public function updater() : ?User
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

    public function tagLinks() : TagLinkCollection
    {
        return $this->getWithProperty(
            $this->tagLinksPropertyName
        );
    }

    // LinkableInterface
    // implemented in Linkable trait

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
        return $this->getWithProperty(
            $this->displayTitlePropertyName
        );
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
