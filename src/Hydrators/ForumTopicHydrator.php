<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ForumTopic;
use App\Parsing\ForumParser;
use App\Parsing\NewsParser;
use App\Repositories\Interfaces\ForumMemberRepositoryInterface;
use App\Repositories\Interfaces\ForumPostRepositoryInterface;
use App\Repositories\Interfaces\ForumRepositoryInterface;
use App\Repositories\Interfaces\ForumTagRepositoryInterface;
use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Parsing\Parsers\CutParser;

class ForumTopicHydrator extends Hydrator
{
    private ForumMemberRepositoryInterface $forumMemberRepository;
    private ForumPostRepositoryInterface $forumPostRepository;
    private ForumRepositoryInterface $forumRepository;
    private ForumTagRepositoryInterface $forumTagRepository;
    private GameRepositoryInterface $gameRepository;

    private CutParser $cutParser;
    private ForumParser $forumParser;
    private LinkerInterface $linker;
    private NewsParser $newsParser;

    private TagsConfigInterface $tagsConfig;

    public function __construct(
        ForumMemberRepositoryInterface $forumMemberRepository,
        ForumPostRepositoryInterface $forumPostRepository,
        ForumRepositoryInterface $forumRepository,
        ForumTagRepositoryInterface $forumTagRepository,
        GameRepositoryInterface $gameRepository,
        CutParser $cutParser,
        ForumParser $forumParser,
        LinkerInterface $linker,
        NewsParser $newsParser,
        TagsConfigInterface $tagsConfig
    )
    {
        $this->forumMemberRepository = $forumMemberRepository;
        $this->forumPostRepository = $forumPostRepository;
        $this->forumRepository = $forumRepository;
        $this->forumTagRepository = $forumTagRepository;
        $this->gameRepository = $gameRepository;

        $this->cutParser = $cutParser;
        $this->forumParser = $forumParser;
        $this->linker = $linker;
        $this->newsParser = $newsParser;

        $this->tagsConfig = $tagsConfig;
    }

    /**
     * @param ForumTopic $entity
     */
    public function hydrate(DbModel $entity) : ForumTopic
    {
        return $entity
            ->withForum(
                fn () => $this->forumRepository->get($entity->forumId)
            )
            ->withGame(
                fn () => $this->gameRepository->getByForum($entity->forum())
            )
            ->withUrl(
                fn () => $this->linker->news($entity->getId())
            )
            ->withForumUrl(
                fn () => $this->linker->forumTopic($entity->getId())
            )
            ->withForumPost(
                fn () => $this->forumPostRepository->getByForumTopic($entity)
            )
            ->withParsedPost(
                $this->frozen(
                    fn () => $this->parsePost($entity)
                )
            )
            ->withDisplayTitle(
                $this->frozen(
                    fn () => $this->newsParser->decodeTopicTitle($entity->title)
                )
            )
            ->withFullText(
                $this->frozen(
                    fn () =>
                    $this->cutParser->full(
                        $entity->parsedPost()
                    )
                )
            )
            ->withShortText(
                $this->frozen(
                    fn () =>
                    $this->cutParser->short(
                        $entity->parsedPost()
                    )
                )
            )
            ->withTags(
                fn () => $this->forumTagRepository->getAllByForumTopic($entity)
            )
            ->withTagLinks(
                fn () =>
                $this->linker->tagLinks(
                    $entity,
                    $this->tagsConfig->getTab(get_class($entity))
                )
            )
            ->withStarterForumMember(
                fn () => $this->forumMemberRepository->get($entity->starterId)
            );
    }

    private function parsePost(ForumTopic $entity) : ?string
    {
        $post = $entity->post();

        if (is_null($post)) {
            return null;
        }

        $post = $this->newsParser->beforeParsePost($post, $entity->getId());
        $post = $this->forumParser->convert(['TEXT' => $post, 'CODE' => 1]);
        $post = $this->newsParser->afterParsePost($post);

        return $post;
    }
}
