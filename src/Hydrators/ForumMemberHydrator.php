<?php

namespace App\Hydrators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\ForumMember;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;

class ForumMemberHydrator extends Hydrator
{
    private LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
    {
        $this->linker = $linker;
    }

    /**
     * @param ForumMember $entity
     */
    public function hydrate(DbModel $entity) : ForumMember
    {
        return $entity
            ->withPageUrl(
                fn () => $this->linker->forumUser($entity->getId())
            );
    }
}
