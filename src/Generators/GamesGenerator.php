<?php

namespace App\Generators;

use App\Repositories\Interfaces\GameRepositoryInterface;
use Plasticode\Generators\EntityGenerator;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator;

class GamesGenerator extends EntityGenerator
{
    private GameRepositoryInterface $gameRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->gameRepository = $container->gameRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['icon'] = $this->optional('url');
        $rules['name'] = $this->rule('text')->gameNameAvailable($id);
        $rules['alias'] = $this->optional('alias')->gameAliasAvailable($id);
        $rules['news_forum_id'] = $this->optional('posInt');
        $rules['main_forum_id'] = $this->optional('posInt');
        $rules['position'] = $this->optional('posInt');
        $rules['parent_id'] = Validator::nonRecursiveParent($this->entity, $id);

        return $rules;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $item['tags'] = $this->buildAutoTags($item);

        $parts = [];

        $gameId = $item[$this->idField];

        $cur = $this->gameRepository->get($gameId);

        while ($cur) {
            $parts[] = $cur->name;
            $cur = $cur->parent();
        }

        $item['select_title'] = implode(' » ', array_reverse($parts));

        return $item;
    }

    private function buildAutoTags(array $item) : string
    {
        $parts = [];

        $gameId = $item[$this->idField];

        $cur = $this->gameRepository->get($gameId);

        while ($cur) {
            $parts[] = $cur->autotags;
            $cur = $cur->parent();
        }

        return implode(', ', array_reverse($parts));
    }
}
