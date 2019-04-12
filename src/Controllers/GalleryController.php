<?php

namespace App\Controllers;

use Illuminate\Support\Arr;

use App\Models\GalleryAuthor;
use App\Models\GalleryPicture;

class GalleryController extends Controller
{
	private $galleryTitle;
	
	public function __construct($container)
	{
		parent::__construct($container);

		$this->galleryTitle = $this->getSettings('gallery.title');
	}

	public function index($request, $response, $args)
	{
		//$groups = GalleryAuthor::getGroups();
		
		$query = GalleryPicture::getPublished();
		$pictures = $this->galleryService->getPage($query)->all();
		
		$lastPic = $pictures->last();

		$params = $this->buildParams([
			//'sidebar' => [ 'stream' ],
			'params' => [
				'title' => $this->galleryTitle,
				//'parts' => $groups,
				'pictures' => $pictures,
				'border_id' => $lastPic ? $lastPic->getId() : null,
			],
		]);
	
		return $this->view->render($response, 'main/gallery/index.twig', $params);
	}

	public function author($request, $response, $args)
	{
		$alias = $args['alias'];

		$author = GalleryAuthor::getPublishedByAlias($alias);

		if (!$author) {
			return $this->notFound($request, $response);
		}
		
		$id = $author->id;

		$query = $author->pictures();
		$pictures = $this->galleryService->getPage($query)->all();
		
		$lastPic = $pictures->last();

		$params = $this->buildParams([
			//'sidebar' => [ 'stream' ],
			'large_image' => $author->displayPicture()
			    ? $this->linker->abs($author->displayPicture()->url())
			    : null,
			'params' => [
				'author' => $author,
				'pictures' => $pictures,
				'border_id' => $lastPic ? $lastPic->getId() : null,
				'title' => $author->fullName(),
				'gallery_title' => $this->galleryTitle,
				'disqus_url' => $this->linker->disqusGalleryAuthor($author),
				'disqus_id' => 'galleryauthor' . $id,
				'rel_prev' => $author->prev() ? $author->prev()->url() : null,
				'rel_next' => $author->next() ? $author->next()->url() : null,
			],
		]);

		return $this->view->render($response, 'main/gallery/author.twig', $params);
	}
	
	public function picture($request, $response, $args)
	{
		$alias = $args['alias'];
		$id = $args['id'];

		$author = GalleryAuthor::getPublishedByAlias($alias);
		
		if (!$author) {
			return $this->notFound($request, $response);
		}
		
		$picture = GalleryPicture::getPublished()->find($id);
		
		if (!$picture) {
			return $this->notFound($request, $response);
		}

		$params = $this->buildParams([
			'game' => $picture->game(),
			'global_context' => true,
			'large_image' => $this->linker->abs($picture->url()),
			'description' => $author->fullName(),
			'params' => [
				'author' => $author,
				'picture' => $picture,
				'title' => $picture->comment,
				'gallery_title' => $this->galleryTitle,
				'rel_next' => $picture->next() ? $picture->next()->pageUrl() : null,
				'rel_prev' => $picture->prev() ? $picture->prev()->pageUrl() : null,
			],
		]);

		return $this->view->render($response, 'main/gallery/picture.twig', $params);
	}
	
	public function chunk($request, $response, $args)
	{
		$borderId = $args['border_id'];

		$authorId = $request->getQueryParam('author_id', null);
		$tag = $request->getQueryParam('tag', null);
		$showAuthor = $request->getQueryParam('show_author', false);

        $borderPic = GalleryPicture::get($borderId);
        
		if ($authorId > 0) {
		    $baseQuery = GalleryPicture::getBasePublishedByAuthor($authorId);
		}
		elseif (strlen($tag) > 0) {
            $baseQuery = GalleryPicture::getBaseByTag($tag);
        }
		
		$query = GalleryPicture::getBefore($borderPic, $baseQuery);

		$pictures = $this->galleryService->getPage($query)->all();
		
		if ($pictures->empty()) {
			return $this->notFound();
		}

		return $this->view->render($response, 'components/gallery_chunk.twig', [
			'pictures' => $pictures,
		    'show_author' => $showAuthor !== false,
		]);
	}
}
