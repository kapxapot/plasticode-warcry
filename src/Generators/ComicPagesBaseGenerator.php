<?php

namespace App\Generators;

use App\Services\ComicService;
use Plasticode\Exceptions\Http\NotFoundException;
use Plasticode\Generators\EntityGenerator;
use Psr\Container\ContainerInterface;

abstract class ComicPagesBaseGenerator extends EntityGenerator
{
    /**
     * Comic service
     *
     * @var \App\Services\ComicService
     */
    private $comicService;
    
    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);
        
        $this->comicService = new ComicService();
    }
    
    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['picture'] = $this->optional('image');
        $rules['thumb'] = $this->rule('image');
        
        return $rules;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);
        
        $item['picture'] = $this->comics->getPictureUrl($item);
        $item['thumb'] = $this->comics->getThumbUrl($item);
        
        unset($item['pic_type']);

        $item['page_url'] = $this->getPageUrl($item);

        return $item;
    }
    
    abstract protected function getPageUrl(array $item) : string;

    public function beforeSave(array $data, $id = null) : array
    {
        $data = parent::beforeSave($data, $id);
        
        if (isset($data['points'])) {
            unset($data['points']);
        }

        if (isset($data['picture'])) {
            unset($data['picture']);
        }

        if (isset($data['thumb'])) {
            unset($data['thumb']);
        }
                
        if (($data['number'] ?? 0) <= 0) {
            $comic = $this->comicService->getComicByContext($data);

            if (!$comic) {
                throw new NotFoundException('Comic not found!');
            }
            
            $data['number'] = $comic->maxPageNumber() + 1;
        }

        return $data;
    }
    
    public function afterSave(array $item, array $data) : void
    {
        parent::afterSave($item, $data);
        
        $this->comics->save($item, $data);
    }
    
    public function afterDelete(array $item) : void
    {
        parent::afterDelete($item);
        
        $this->comics->delete($item);
    }
}
