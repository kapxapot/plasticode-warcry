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
		$groups = GalleryAuthor::getGroups();

		$params = $this->buildParams([
			'sidebar' => [ 'stream' ],
			'params' => [
				'title' => $this->galleryTitle,
				'parts' => $groups,
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

		// paging...
		$totalCount = $author->count();
		$picsPerPage = $this->getSettings('gallery.pics_per_page');
		$totalPages = ceil($totalCount / $picsPerPage);
	
		// determine page
		$page = $request->getQueryParam('page', 1);
		
		if (!is_numeric($page) || $page < 2) {
			$page = 1;
		}
	
		if ($page > $totalPages) {
			$page = $totalPages;
		}
		
		// paging itself
		$baseUrl = $author->pageUrl;
		$paging = $this->pagination->simple($baseUrl, $totalPages, $page);

		// pics
		$offset = ($page - 1) * $picsPerPage;

		$params = $this->buildParams([
			'sidebar' => [ 'stream' ],
			'image' => $author->displayPicture()
			    ? $this->linker->abs($author->displayPicture()->thumbUrl())
			    : null,
			'params' => [
				'author' => $author,
				'pictures' => $author->pictures($offset, $picsPerPage),
				'paging' => $paging,
				'title' => $author->fullName(),
				'gallery_title' => $this->galleryTitle,
				'disqus_url' => $this->linker->disqusGalleryAuthor($author),
				'disqus_id' => 'galleryauthor' . $id,
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
		
		$picture = GalleryPicture::getPublished($id);
		
		if (!$picture) {
			return $this->notFound($request, $response);
		}

		$params = $this->buildParams([
			'game' => $picture->game(),
			'global_context' => true,
			'image' => $this->linker->abs($picture->thumbUrl()),
			'description' => $author->fullName(),
			'params' => [
				'author' => $author,
				'picture' => $picture,
				'title' => $picture->comment,
				'gallery_title' => $this->galleryTitle,
				'rel_prev' => Arr::get($picture, 'prev.page_url'),
				'rel_next' => Arr::get($picture, 'next.page_url'),
			],
		]);

		return $this->view->render($response, 'main/gallery/picture.twig', $params);
	}
}
