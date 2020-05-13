<?php

namespace App\Generators;

use App\Core\Interfaces\LinkerInterface;
use App\Models\GalleryAuthor;
use App\Repositories\Interfaces\GalleryAuthorRepositoryInterface;
use Plasticode\Gallery\Gallery;
use Plasticode\Generators\TaggableEntityGenerator;
use Plasticode\Generators\Traits\Publishable;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

class GalleryPicturesGenerator extends TaggableEntityGenerator
{
    use Publishable
    {
        beforeSave as protected publishableBeforeSave;
    }

    private GalleryAuthorRepositoryInterface $galleryAuthorRepository;

    private Gallery $gallery;
    private LinkerInterface $linker;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->galleryAuthorRepository = $container->galleryAuthorRepository;

        $this->gallery = $container->gallery;
        $this->linker = $container->linker;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules['comment'] = $this->rule('text');
        $rules['picture'] = $this->optional('image');
        $rules['thumb'] = $this->rule('image');

        return $rules;
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['uri'] = 'gallery_authors/{id:\d+}/gallery_pictures';
        $options['filter'] = 'author_id';
        $options['admin_uri'] = 'gallery/{id:\d+}/gallery_pictures';
        $options['admin_template'] = 'gallery_pictures';
        $options['admin_args'] = [
            'upload_path' => 'admin.gallery.upload',
        ];

        return $options;
    }
    
    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $item['picture'] = $this->linker->abs($this->gallery->getPictureUrl($item));
        $item['thumb'] = $this->linker->abs($this->gallery->getThumbUrl($item));

        unset($item['picture_type']);
        unset($item['thumb_type']);

        if ($item['points']) {
            $item['points'] = Strings::explode($item['points']);
        }

        $authorId = $item['author_id'];

        $author = $this->galleryAuthorRepository->get($authorId);

        if ($author) {
            $item['author_alias'] = $author->alias;
        }

        return $item;
    }

    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $authorId = $args['id'];

        $author = $this->galleryAuthorRepository->get($authorId);

        $params['source'] = 'gallery_authors/' . $authorId . '/gallery_pictures';

        $params['breadcrumbs'] = [
            [
                'text' => 'Галерея',
                'link' => $this->router->pathFor('admin.entities.gallery_authors')
            ],
            ['text' => $author->name],
            ['text' => 'Картинки'],
        ];

        $params['hidden'] = [
            'author_id' => $authorId,
        ];

        $params['upload_context'] = [
            'field' => 'author_id',
            'id' => $authorId,
        ];

        return $params;
    }

    public function beforeSave(array $data, $id = null) : array
    {
        $data = $this->publishableBeforeSave($data, $id);

        if (isset($data['points'])) {
            $data['points'] = implode(',', $data['points']);
        }

        if (isset($data['picture'])) {
            unset($data['picture']);
        }

        if (isset($data['thumb'])) {
            unset($data['thumb']);
        }

        return $data;
    }

    public function afterSave(array $item, array $data) : void
    {
        parent::afterSave($item, $data);

        $this->gallery->save($item, $data);
    }

    public function afterDelete(array $item) : void
    {
        parent::afterDelete($item);

        $this->gallery->delete($item);
    }
}
