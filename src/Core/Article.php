<?php

namespace App\Core;

use Plasticode\Contained;
use Plasticode\Util\Arrays;
use Plasticode\Util\Strings;

class Article extends Contained
{
	public $id;

	public $data;
	public $parsed;
	
	public $breadcrumbs;
	public $contents;
	public $text;

	public function __construct($container, $id, $cat, $rebuild = false)
	{
		parent::__construct($container);
		
		$id = Strings::toSpaces($id);
		$cat = Strings::toSpaces($cat);

		$this->data = $this->db->getArticle($id, $cat);
		
		if ($this->data) {
	        $this->id = $this->data['id'];
	        
	        $text = $this->data['text'];
	
			$this->breadcrumbs = $this->buildBreadcrumbs();

    		if (!$rebuild && strlen($this->data['cache']) > 0) {
                $parsed = @json_decode($this->data['cache']);
    		}
    		
    		if (!is_array($parsed) && strlen($text) > 0) {
    			$parsed = $this->parser->parse($text);
    
    			$this->db->saveArticleCache($this->id, json_encode($parsed));
    		}
    		
    		if ($parsed) {
        		$this->parsed = $parsed;
        		
    			$this->text = $parsed['text'];
    			$this->contents = $parsed['contents'];
            }

			/*if (!$rebuild && (strlen($this->data['cache']) > 0)) {
				$this->text = $this->data['cache'];
				$this->contents = json_decode($this->data['contents_cache']);
			}
			elseif (strlen($text) > 0) {
				$result = $this->parser->parse($text);

				$this->text = $result['text'];
				$this->contents = $result['contents'];
				$this->data['card_image'] = Arrays::first($result['images']);

				$this->db->saveArticleCache($this->id, $this->text);
				$this->db->saveArticleContentsCache($this->id, json_encode($this->contents));
				$this->db->saveArticleCardImage($this->id, $this->data['card_image']);
			}*/
		}
	}

	private function buildBreadcrumbs()
	{
		$curId = $this->data['parent_id'];
		
		while ($curId > 0) {
			$article = $this->db->getArticle($curId);
			
			if (!$article) {
				break;
			}

			if (!$article['no_breadcrumb']) {
				$link = $this->builder->buildArticleLink($article);
				$linkArray[] = [
					'url' => $link['url'],
					'text' => $link['title'],
					'title' => $link['title_en']
				];
			}

			$curId = $article['parent_id'];
		}
		
		return $linkArray ? array_reverse($linkArray) : null;
	}
}
