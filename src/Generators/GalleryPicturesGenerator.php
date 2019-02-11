<?php

namespace App\Generators;

use Plasticode\Generators\EntityGenerator;
use Plasticode\Traits\Publishable;

use App\Data\Taggable;

class GalleryPicturesGenerator extends EntityGenerator
{
	use Publishable;
	
	protected $taggable = Taggable::GALLERY_PICTURES;
	
	public function getRules($data, $id = null)
	{
	    $rules = parent::getRules($data, $id);
	    
		$rules['comment'] = $this->rule('text');
		$rules['picture'] = $this->optional('image');
		$rules['thumb'] = $this->rule('image');
		
		return $rules;
	}
	
	public function getOptions()
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
	
	public function afterLoad($item)
	{
		$item['picture'] = $this->gallery->getPictureUrl($item);
		$item['thumb'] = $this->gallery->getThumbUrl($item);
		
		unset($item['picture_type']);
		unset($item['thumb_type']);
		
		if ($item['points']) {
			$item['points'] = explode(',', $item['points']);
		}

		$author = $this->db->getGalleryAuthor($item['author_id']);
		if ($author) {
			$item['author_alias'] = $author['alias'];
		}

		return $item;
	}

	public function getAdminParams($args)
	{
		$params = parent::getAdminParams($args);

		$authorId = $args['id'];
		$author = $this->db->getGalleryAuthor($authorId, true);

		$params['source'] = "gallery_authors/{$authorId}/gallery_pictures";
		$params['breadcrumbs'] = [
			[ 'text' => 'Галерея', 'link' => $this->router->pathFor('admin.entities.gallery_authors') ],
			[ 'text' => $author['name'] ],
			[ 'text' => 'Картинки' ],
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
	
	public function beforeSave($data, $id = null)
	{
		if (isset($data['points'])) {
			$data['points'] = implode(',', $data['points']);
		}

		if (isset($data['picture'])) {
			unset($data['picture']);
		}

		if (isset($data['thumb'])) {
			unset($data['thumb']);
		}

		$data = $this->publishIfNeeded($data);		
		
		return $data;
	}
	
	public function afterSave($item, $data)
	{
		$this->gallery->save($item, $data);
	}
	
	public function afterDelete($item)
	{
		$this->gallery->delete($item);
	}
}
