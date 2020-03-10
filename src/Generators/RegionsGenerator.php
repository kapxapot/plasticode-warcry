<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as v;

class RegionsGenerator extends EntityGenerator
{
    /** @var RegionRepositoryInterface */
    private $regionRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->regionRepository = $container->regionRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['parent_id'] = v::nonRecursiveParent($this->entity, $id);
        
        return $rules;
    }
    
    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $parts = [];
        
        $cur = $this->regionRepository->get($item[$this->idField]);
        
        while ($cur != null) {
            $parts[] = $cur->nameRu;
            
            $cur = $cur->terminal
                ? null
                : $cur->parent();
        }

        $item['select_title'] = implode(' » ', array_reverse($parts));

        return $item;
    }
}
