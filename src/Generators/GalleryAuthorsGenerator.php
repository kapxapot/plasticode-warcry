<?php

namespace App\Generators;

use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Generators\EntityGenerator;
use Psr\Container\ContainerInterface;

class GalleryAuthorsGenerator extends EntityGenerator
{
    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->galleryAuthorRepository = $container->galleryAuthorRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules['name'] = $this->rule('text')->galleryAuthorNameAvailable($id);

        if (array_key_exists('alias', $data)) {
            $rules['alias'] = $this->rule('alias')->galleryAuthorAliasAvailable($id);
        }

        return $rules;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['admin_uri'] = 'gallery';
        $options['admin_template'] = 'entity_with_upload';
        $options['admin_args'] = [
            'upload_path' => 'admin.gallery.upload',
        ];

        return $options;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField];

        $author = $this->galleryAuthorRepository->get($id);

        $item['page_url'] = $author->pageUrl();
        $item['context_field'] = 'author_id';

        return $item;
    }
}
