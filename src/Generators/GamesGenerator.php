<?php

namespace App\Generators;

use App\Models\Game;
use Plasticode\Generators\EntityGenerator;
use Respect\Validation\Validator as v;

class GamesGenerator extends EntityGenerator
{
    public function getRules($data, $id = null)
    {
        $rules = parent::getRules($data, $id);
        
        $rules['icon'] = $this->optional('url');
        $rules['name'] = $this->rule('text')->gameNameAvailable($id);
        $rules['alias'] = $this->optional('alias')->gameAliasAvailable($id);
        $rules['news_forum_id'] = $this->optional('posInt');
        $rules['main_forum_id'] = $this->optional('posInt');
        $rules['position'] = $this->optional('posInt');
        $rules['parent_id'] = v::nonRecursiveParent($this->entity, $id);
        
        return $rules;
    }
    
    public function afterLoad($item)
    {
        $item = parent::afterLoad($item);
        
        $item['tags'] = $this->buildAutoTags($item);

        $parts = [];

        $cur = Game::get($item['id']);
        
        while ($cur) {
            $parts[] = $cur->name;
            $cur = $cur->parent();
        }

        $item['select_title'] = implode(' » ', array_reverse($parts));
        
        return $item;
    }
    
    private function buildAutoTags($item)
    {
        $parts = [];
        
        $cur = Game::get($item['id']);
        
        while ($cur) {
            $parts[] = $cur->autotags;
            $cur = $cur->parent();
        }
        
        return implode(', ', array_reverse($parts));
    }
}
