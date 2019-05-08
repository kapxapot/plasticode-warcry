<?php

namespace App\Core;

use Plasticode\Core\Linker as LinkerBase;
use Plasticode\Util\Strings;

use App\Models\GalleryPicture;

class Linker extends LinkerBase
{
	// urls
	private function forumUrl($url)
	{
		return $this->getSettings('forum.page') . '?' . $url;
	}

	// site
	public function article($id = null, $cat = null)
	{
		$params = [ 'id' => Strings::fromSpaces($id) ];
		
		if ($cat) {
			$params['cat'] = Strings::fromSpaces($cat);
		}

		return $this->router->pathFor('main.article', $params);
	}

	public function news($id = null)
	{
		return $this->router->pathFor('main.news', [ 'id' => $id ]);
	}
	
	public function newsYear(int $year)
	{
	    return $this->router->pathFor('main.news.archive.year', [ 'year' => $year ]);
	}

	public function event($id = null)
	{
		return $this->router->pathFor('main.event', [ 'id' => $id ]);
	}

	public function video($id = null)
	{
		return $this->router->pathFor('main.video', [ 'id' => $id ]);
	}

	public function n($id)
	{
		return $this->abs('n/' . $id);
	}

	public function game($game)
	{
		$params = [];
		
		if ($game && !$game->default()) {
			$params['game'] = $game->alias;
		}
		
		return $this->router->pathFor('main.index', $params);
	}
	
	// disqus
	public function disqusNews($id)
	{
		return $this->abs($this->news($id));
	}
	
	public function disqusArticle($article)
	{
		$id = $article->nameEn;
		
		if ($article->category() != null) {
			$cat = $article->category()->nameEn;
		}

		return $this->abs($this->article($id, $cat));
	}
	
	public function disqusGalleryAuthor($author)
	{
		return $this->abs($this->galleryAuthor($author));
	}

	public function disqusRecipes($skill)
	{
		return $this->abs($this->recipes($skill));
	}

	public function disqusRecipe($id)
	{
		return $this->abs($this->recipe($id));
	}

	// forum
	public function forumTag($text)
	{
		return $this->forumUrl('app=core&module=search&do=search&search_tags=' . urlencode($text) . '&search_app=forums');
	}
	
	public function forumUser($id)
	{
		return $this->forumUrl('showuser=' . $id);
	}
	
	/*public function forumNewsIndex()
	{
		$index = $this->getSettings('forum.news_index');
	
		return $this->forumUrl('showforum=' . $index);
	}*/
	
	public function forumTopic($id, $new = false)
	{
		$appendix = $new ? '&view=getnewpost' : '';
		
		return $this->forumUrl('showtopic=' . $id . $appendix);
	}
	
	public function forumUpload($name)
	{
		return $this->getSettings('forum.index') . '/uploads/' . $name;
	}
	
	// gallery
	public function galleryAuthor($alias)
	{
		return $this->router->pathFor('main.gallery.author', [ 'alias' => $alias ]);
	}

	public function galleryPictureImg(GalleryPicture $picture)
	{
	    return $this->gallery->getPictureUrl($picture);
		/*$ext = $this->getExtension($picture->pictureType);
		return $this->getSettings('folders.gallery_pictures_public') . $picture->id . '.' . $ext;*/
	}
	
	public function galleryThumbImg(GalleryPicture $picture)
	{
	    return $this->gallery->getThumbUrl($picture);
		/*$ext = $this->getExtension($picture->thumbType);
		return $this->getSettings('folders.gallery_thumbs_public') . $picture->id . '.' . $ext;*/
	}
	
	public function galleryPicture($alias, $id)
	{
		return $this->router->pathFor('main.gallery.picture', [ 'alias' => $alias, 'id' => $id ]);
	}
	
	// streams
	public function stream($alias = null)
	{
		return $this->router->pathFor('main.stream', [ 'alias' => $alias ]);
	}
	
	// paging
	function page($base, $page)
	{
		$delim = strpos($base, '?') !== false ? '&' : '?';
		return $base . ($page == 1 ? '' : "{$delim}page={$page}");
	}
	
	// comics
	public function comicSeries($series)
	{
		return $this->router->pathFor('main.comics.series', [ 'alias' => $series->alias ]);
	}

	public function comicIssue($comic)
	{
	    $series = $comic->series();
	    
	    if (is_null($series)) {
	        throw new \Exception("Comic issue {$comic} has no comic series.");
	    }
	    
		return $this->router->pathFor('main.comics.issue', [
		    'alias' => $series->alias,
		    'number' => $comic->number,
		]);
	}

	public function comicIssuePage($page)
	{
	    $comic = $page->comic();
	    
	    if (is_null($comic)) {
	        dd($page);
	        
	        throw new \Exception("Comic issue page {$page} has no comic issue.");
	    }
	    
	    $series = $comic->series();
	    
	    if (is_null($series)) {
	        throw new \Exception("Comic issue {$comic} has no comic series.");
	    }
	    
		return $this->router->pathFor('main.comics.issue.page', [
			'alias' => $series->alias,
			'number' => $comic->number,
			'page' => $page->number,
		]);
	}

	public function comicStandalone($comic)
	{
		return $this->router->pathFor('main.comics.standalone', [ 'alias' => $comic->alias ]);
	}

	public function comicStandalonePage($page)
	{
	    $comic = $page->comic();
	    
	    if (is_null($comic)) {
	        throw new \Exception("Comic standalone page {$page} has no comic standalone.");
	    }
	    
		return $this->router->pathFor('main.comics.standalone.page', [
			'alias' => $comic->alias,
			'page' => $page->number,
		]);
	}

	public function comicPageImg($page)
	{
		$ext = $this->getExtension($page->type);
		return $this->getSettings('folders.comics_pages_public') . $page->id . '.' . $ext;
	}
	
	public function comicThumbImg($page)
	{
		$ext = $this->getExtension($page->type);
		return $this->getSettings('folders.comics_thumbs_public') . $page->id . '.' . $ext;
	}
	
	// recipes
	public function recipes($skill = null)
	{
		$params = [];
		
		if ($skill) {
			$params['skill'] = $skill['alias'];
		}
		
		return $this->router->pathFor('main.recipes', $params);
	}
	
	public function recipe($id)
	{
		return $this->router->pathFor('main.recipe', [ 'id' => $id ]);
	}
	
	// wowhead
	public function wowheadIcon($icon)
	{
		$icon = strtolower($icon);
		return "//static.wowhead.com/images/wow/icons/medium/{$icon}.jpg";
	}
	
	private function wowheadUrl($params)
	{
		return $this->getSettings('webdb_link') . $params;
	}
	
	private function wowheadUrlRu($params)
	{
		return $this->getSettings('webdb_ru_link') . $params;
	}
	
	public function wowheadSpellRu($id)
	{
		return $this->wowheadUrlRu('spell=' . $id);
	}
	
	public function wowheadItemRu($id)
	{
		return $this->wowheadUrlRu('item=' . $id);
	}
	
	public function wowheadItem($id)
	{
		return $this->wowheadUrl('item=' . $id);
	}
	
	public function wowheadItemXml($id)
	{
		return $this->wowheadItem($id) . '&xml';
	}
	
	public function wowheadItemRuXml($id)
	{
		return $this->wowheadItemRu($id) . '&xml';
	}
	
	// hs
	public function hsCard($id)
	{
		return $this->getSettings('hsdb_ru_link') . 'cards/' . $id;
	}
}
