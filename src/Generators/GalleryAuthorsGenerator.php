<?php

namespace App\Generators;

use App\Models\GalleryAuthor;
use Plasticode\Generators\EntityGenerator;

class GalleryAuthorsGenerator extends EntityGenerator
{
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

        $item['context_field'] = 'author_id';

        $author = GalleryAuthor::get($item[$this->idField]);

        $item['page_url'] = $author->pageUrl();

        return $item;
    }
}
