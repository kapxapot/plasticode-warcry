<?php

namespace App\Hydrators;

use App\Models\User;
use App\Repositories\Interfaces\ForumMemberRepositoryInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\External\Gravatar;
use Plasticode\Hydrators\UserHydrator as BaseUserHydrator;
use Plasticode\Models\DbModel;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator extends BaseUserHydrator
{
    private ForumMemberRepositoryInterface $forumMemberRepository;

    public function __construct(
        ForumMemberRepositoryInterface $forumMemberRepository,
        RoleRepositoryInterface $roleRepository,
        LinkerInterface $linker,
        Gravatar $gravatar
    )
    {
        parent::__construct(
            $roleRepository,
            $linker,
            $gravatar
        );

        $this->forumMemberRepository = $forumMemberRepository;
    }

    /**
     * @param User $entity
     */
    public function hydrate(DbModel $entity) : User
    {
        /** @var User */
        $entity = parent::hydrate($entity);

        return $entity
            ->withForumMember(
                fn () =>
                $this
                    ->forumMemberRepository
                    ->getByName($entity->forumName ?? $entity->login)
            );
    }
}
