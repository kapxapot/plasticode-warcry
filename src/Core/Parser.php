<?php

namespace App\Core;

use Plasticode\Core\Parser as ParserBase;
use Plasticode\Util\Strings;

class Parser extends ParserBase {
	protected function getWebDbLink($appendix) {
		return $this->getSettings('webdb_ru_link') . $appendix;
	}

	protected function parseMore($text) {
		$newText = '';
		
		$parts = preg_split('/(\[\[.*\]\])/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		foreach ($parts as $part) {
			$parsed = null;
			
			if (preg_match('/\[\[(.*)\]\]/', $part, $matches)) {
				$match = $matches[1];
				
				if (strlen($match) > 0) {
					$parsed = $this->parseDoubleBracketsMatch($match);
				}
			}
			
			$newText .= $parsed ?? $part;
		}
		
		return $newText;
	}
	
	protected function parseDoubleBracketsMatch($match) {
		$text = null;
		
		$mappings = [ 'ach' => 'achievement' ];
		
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
			$article = $this->db->getArticle($id, $cat);

			$text = $article
				? $this->decorator->articleUrl($name, $id, $idEsc, $cat, $catEsc, true)
				: $this->decorator->noArticleUrl($name, $id, $cat);
		}
		else {
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
				$text = $this->decorator->url($url, $content, null, null, null, [ 'wowhead' => $urlChunk ]);

				// special treatment
				switch ($tag) {
					case 'item':
						if ($id > 0) {
							$sources = $this->db->getRecipesByItemId($id);
							if (is_array($sources) && count($sources) > 0) {
								$recipeData = $sources[0];
								$title = 'Рецепт: ' . $recipeData['name_ru'];
								$rel = 'spell=' . $recipeData['id'] . '&amp;domain=ru';
								
								$recipeUrl = $this->decorator->recipePageUrl($recipeData['id'], $title, $rel);
					
								// adding
								$text .= '&nbsp;' . $recipeUrl;
							}
						}

						break;

					case 'spell':
						// is spell is a recipe, link it to our recipe page
						$recipe = $this->db->getRecipe($id);
						
						if ($recipe) {
							$title = 'Рецепт: ' . $content; // $id
							$rel = 'spell=' . $id . '&amp;domain=ru';
							$recipeUrl = $this->decorator->recipePageUrl($id, $title, $rel, $content);
					
							// rewriting default
							$text = $recipeUrl;
						}

						break;

					case 'coords':
						if ($chunksCount > 2) {
							$x = $chunks[1];
							$y = $chunks[2];
							
							$coordsLink = null;
							$coordsText = $this->decorator->coordsBlock($x, $y);
	
							if (!is_numeric($id)) {
								$id = $this->db->getLocationId($id);
							}
	
							if ($id > 0) {
								$coords = '';
								if ($x > 0 && $y > 0) {
									$coords = ':' . ($x * 10) . ($y * 10);
								}
								
								$url = $this->getWebDbLink('maps?data=' . $id . $coords);
								$text = $this->decorator->url($url, $coordsText);
							}
						}

						break;

					case 'card':
						$url = $this->getSettings('hsdb_ru_link') . 'cards/' . $id;
						$text = $this->decorator->url($url, $content, null, 'hh-ttp');

						break;

					case 'news':
					case 'event':
					case 'stream':
						$text = $this->decorator->entityUrl("%{$tag}%/{$id}", $content);

						break;

					case 'tag':
						$id = Strings::fromSpaces($id, '+');
						$text = $this->decorator->entityUrl("%{$tag}%/{$id}", $content);

						break;

				}
			}
		}
		
		return (strlen($text) > 0) ? $text : null;
	}

	protected function parseBrackets($text) {
		$text = parent::parseBrackets($text);

		$text = $this->parseQuoteBB($text, 'bluepost', [ $this->decorator, 'bluepostBlock' ], 'Blizzard');

		return $text;
	}

	public function renderLinks($text) {
		$text = str_replace('%article%/', $this->linker->article(), $text);
		$text = str_replace('%news%/', $this->linker->news(), $text);
		$text = str_replace('%stream%/', $this->linker->stream(), $text);
		$text = str_replace('%event%/', $this->linker->event(), $text);
		$text = str_replace('%tag%/', $this->linker->tag(), $text);

		return $text;
	}
}
