<?php

namespace App\Core;

use Plasticode\Collection;
use Plasticode\Core\Parser as ParserBase;
use Plasticode\Util\Arrays;
use Plasticode\Util\Numbers;
use Plasticode\Util\Strings;

use App\Models\Article;
use App\Models\GalleryPicture;
use App\Models\Location;
use App\Models\Recipe;

class Parser extends ParserBase
{
	protected function getWebDbLink($appendix)
	{
		return $this->getSettings('webdb_ru_link') . $appendix;
	}

	protected function parseMore($text)
	{
		return preg_replace_callback(
		    '/\[\[(.*)\]\]/U',
			function ($matches) {
			    $original = $matches[0];
				$match = $matches[1];
				
				$parsed = $this->parseDoubleBracketsMatch($match);

				return $parsed ?? $original;
			},
			$text
		);
	}
	
	protected function parseDoubleBracketsMatch($match)
	{
	    if (strlen($match) == 0) {
	        return null;
	    }
	    
		$text = null;
		
		$mappings = [
		    'ach' => 'achievement',
		    'wowevent' => 'event',
		];
		
		$chunks = preg_split('/\|/', $match);
		$chunksCount = count($chunks);

		// анализируем первый элемент на наличие двоеточия ":"
		$tagChunk = $chunks[0];
		$tagParts = preg_split('/:/', $tagChunk);
		$tag = $tagParts[0];
		
		if (count($tagParts) == 1) {
			// статья
			$id = $tagChunk;
			$cat = '';
			$name = $chunks[$chunksCount - 1];

			if ($chunksCount > 2) {
				$cat = $chunks[1];
			}

			$idEsc = Strings::fromSpaces($id);
			$catEsc = Strings::fromSpaces($cat);
			$article = Article::getByName($id, $cat);

			$text = ($article && $article->isPublished())
				? $this->renderer->articleUrl($name, $id, $idEsc, $cat, $catEsc, true)
				: $this->renderer->noArticleUrl($name, $id, $cat);
		} else {
			// тег с id
			// [[npc:27412|Слинкин Демогном]]
			// [[tag:id|content]]
			
			$id = $tagParts[1];

			if (strlen($id) > 0) {
				$content = ($chunksCount > 1) ? $chunks[1] : $id;

				// default text for tags
				// in most cases it's exactly what's needed
				$dbTag = $mappings[$tag] ?? $tag;
				$urlChunk = $dbTag . '=' . $id;
				$url = $this->getWebDbLink($urlChunk);
				$text = $this->render('url', [
				    'url' => $url,
				    'text' => $content,
				    'data' => [ 'wowhead' => $urlChunk ],
				]);

				// special treatment
				switch ($tag) {
					case 'item':
						if ($id > 0) {
							$sources = Recipe::getAllByItemId($id);
							
							if ($sources->any()) {
								$recipe = $sources->first();
								
								$title = 'Рецепт: ' . $recipe->nameRu;
								$rel = 'spell=' . $recipe->getId() . '&amp;domain=ru';
								
								$url = $this->linker->recipe($recipe->getId());
								$recipeUrl = $this->renderer->recipePageUrl($url, $title, $rel);
					
								// adding
								$text .= '&nbsp;' . $recipeUrl;
							}
						}

						break;

					case 'spell':
						// is spell is a recipe, link it to our recipe page
						$recipe = Recipe::get($id);
						
						if ($recipe) {
							$title = 'Рецепт: ' . $content; // $id
							$rel = 'spell=' . $id . '&amp;domain=ru';
							$url = $this->linker->recipe($id);
							$recipeUrl = $this->renderer->recipePageUrl($url, $title, $rel, $content);
					
							// rewriting default
							$text = $recipeUrl;
						}

						break;

					case 'coords':
						if ($chunksCount > 2) {
							$x = $chunks[1];
							$y = $chunks[2];
							
							$coordsLink = null;
							$coordsText = '[' . round($x) . ',&nbsp;' . round($y) . ']';
	
							if (!is_numeric($id)) {
								$location = Location::getByName($id);
								
								if ($location) {
								    $id = $location->getId();
								}
							}
	
							if ($id > 0) {
								$coords = '';
								
								$x = Numbers::parseFloat($x);
								$y = Numbers::parseFloat($y);
								
								if ($x > 0 && $y > 0) {
									$coords = ':' . ($x * 10) . ($y * 10);
								}
								
								$url = $this->getWebDbLink('maps?data=' . $id . $coords);
								$text = $this->render('url', [
								    'url' => $url,
								    'text' => $coordsText,
								]);
							}
						}

						break;

					case 'card':
						$url = $this->getSettings('hsdb_ru_link') . 'cards/' . $id;
						$text = $this->render('url', [
						    'url' => $url,
						    'text' => $content,
						    'style' => 'hh-ttp',
						]);
						break;

					case 'news':
					case 'event':
					case 'stream':
						$text = $this->renderer->entityUrl("%{$tag}%/{$id}", $content);
						break;

					case 'tag':
						$id = Strings::fromSpaces($id, '+');
						$text = $this->renderer->entityUrl("%{$tag}%/{$id}", $content);
						break;
                    
                    case 'gallery':
                        $pictures = Collection::makeEmpty();
                        
                        $ids = explode(',', $id);
                        
                        foreach ($ids as $id) {
                            if (is_numeric($id)) {
                                $pic = GalleryPicture::get($id);
                                
                                if ($pic) {
                                    $pictures = $pictures->add($pic);
                                }
                            } else {
                                $tagPics = GalleryPicture::getByTag($id);
                                $pictures = $pictures->concat($tagPics);
                            }
                        }

                        $text = $pictures->any()
                            ? $this->render('gallery', [
                                'pictures' => $pictures->distinct(),
                                'lightbox' => true
                            ])
                            : null;

                        break;
				}
			}
		}
		
		return (strlen($text) > 0) ? $text : null;
	}
	
	protected function enrichBluepostData($data)
	{
	    $data['author'] = $data['author'] ?? 'Blizzard';
	    $data['style'] = 'quote--bluepost';
	    
	    return $data;
	}

	/*protected function parseBrackets($result)
	{
		$result = parent::parseBrackets($result);

		$result['text'] = $this->parseQuoteBB($result['text'], 'bluepost', [ $this, 'enrichBluepostData' ]);

		return $result;
	}*/
	
	protected function renderBBContainer($node)
	{
	    switch ($node['tag']) {
            case 'bluepost':
	            return $this->renderBBNode('quote', $node, function ($content, $attrs) {
	                $data = $this->mapQuoteBB($content, $attrs);
	                $data = $this->enrichBluepostData($data);
	                
	                return $data;
                });
	            
            default:
                return parent::renderBBContainer($node);
	    }
	}
	
	protected function getBBContainerTags()
	{
	    return array_merge(parent::getBBContainerTags(), [ 'bluepost' ]);
	}

	public function renderLinks($text)
	{
		$text = str_replace('%article%/', $this->linker->article(), $text);
		$text = str_replace('%news%/', $this->linker->news(), $text);
		$text = str_replace('%stream%/', $this->linker->stream(), $text);
		$text = str_replace('%event%/', $this->linker->event(), $text);
		$text = str_replace('%tag%/', $this->linker->tag(), $text);

		return $text;
	}
}
