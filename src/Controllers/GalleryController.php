<?php

namespace App\Controllers;

use Illuminate\Support\Arr;

class GalleryController extends BaseController {
	private $galleryTitle;
	
	public function __construct($container) {
		parent::__construct($container);

		$this->galleryTitle = $this->getSettings('gallery.title');
	}

	public function index($request, $response, $args) {
		$authors = $this->builder->buildSortedGalleryAuthors();

		$params = $this->buildParams([
			'sidebar' => [ 'stream' ],
			'params' => [
				'title' => $this->galleryTitle,
				'authors' => $authors,
				'forum_index' => $this->getSettings('forum.index'),
			],
		]);
	
		return $this->view->render($response, 'main/gallery/index.twig', $params);
	}

	public function author($request, $response, $args) {
		$alias = $args['alias'];

		$row = $this->db->getGalleryAuthorByAlias($alias);
		
		if (!$row) {
			return $this->notFound($request, $response);
		}
		
		$id = $row['id'];

		$author = $this->builder->buildGalleryAuthor($row);

		// paging...
		$totalCount = $author['count'];
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
		$baseUrl = $author['page_url'];
		$paging = $this->builder->buildPaging($baseUrl, $totalPages, $page);

		// pics
		$offset = ($page - 1) * $picsPerPage;
		
		$picRows = $this->db->getGalleryPictures($id, $offset, $picsPerPage);
		
		$pictures = [];
		
		foreach ($picRows as $picRow) {
			$pictures[] = $this->builder->buildGalleryPicture($picRow, $author);
		}

		$params = $this->buildParams([
			'sidebar' => [ 'stream' ],
			'params' => [
				'author' => $author,
				'pictures' => $pictures,
				'paging' => $paging,
				'title' => $author['name'],
				'gallery_title' => $this->galleryTitle,
				'disqus_url' => $this->linker->disqusGalleryAuthor($author),
				'disqus_id' => 'galleryauthor' . $id,
			],
		]);

		return $this->view->render($response, 'main/gallery/author.twig', $params);
	}
	
	public function picture($request, $response, $args) {
		$alias = $args['alias'];
		$id = $args['id'];

		$authorRow = $this->db->getGalleryAuthorByAlias($alias);
		
		if (!$authorRow) {
			return $this->notFound($request, $response);
		}
		
		$author = $this->builder->buildGalleryAuthor($authorRow);
		
		$row = $this->db->getGalleryPicture($id);
		
		if (!$row) {
			return $this->notFound($request, $response);
		}

		$picture = $this->builder->buildGalleryPicture($row);

		$params = $this->buildParams([
			'params' => [
				'author' => $author,
				'picture' => $picture,
				'title' => $picture['comment'],
				'gallery_title' => $this->galleryTitle,
				'rel_prev' => Arr::get($picture, 'prev.page_url'),
				'rel_next' => Arr::get($picture, 'next.page_url'),
			],
		]);

		return $this->view->render($response, 'main/gallery/picture.twig', $params);
	}
}
