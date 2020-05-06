<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Models\User as BaseUser;
use Plasticode\Repositories\Idiorm\UserRepository as BaseUserRepository;

class UserRepository extends BaseUserRepository implements UserRepositoryInterface
{
    protected string $entityClass = User::class;

    public function get(?int $id) : ?User
    {
        return parent::get($id);
    }

    public function create(array $data) : User
    {
        return parent::create($data);
    }

    public function save(BaseUser $user) : User
    {
        return parent::save($user);
    }

    public function getByLogin(string $login) : ?User
    {
        return parent::getByLogin($login);
    }
}
