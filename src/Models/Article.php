<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\DbModel;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\Children;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Sort;
use Plasticode\Util\Strings;

class Article extends DbModel
{
    use CachedDescription, Children, FullPublish, Stamps, Tags;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getDescriptionField()
    {
        return 'text';
    }

    // getters - many
    
    public static function getByName($name, $cat = null)
    {
		$name = Strings::toSpaces($name);
		$cat = Strings::toSpaces($cat);

		return self::getProtected(null, function($q) use ($name, $cat) {
			if (is_numeric($name)) {
				$q = $q->where('id', $name);
			}
			else {
				$q = $q->where('name_en', $name);
	
				if ($cat) {
				    $category = ArticleCategory::getByName($cat);
				    
				    if ($category) {
					    $catId = $category->id;
				    }
				}
	
				if ($catId) {
					$q = $q
						->whereRaw('(cat = ? or cat is null)', [ $catId ])
						->orderByDesc('cat');
				}
				else {
					$q = $q->orderByAsc('cat');
				}
			}
	
			return $q;
		});
	}

    public static function getLatest($game, $limit, $exceptArticleId)
    {
		return self::getAllPublished(function ($query) use ($game, $limit, $exceptArticleId) {
		    $query = $query->where('announce', 1);
			
    		if ($game) {
    			$query = $game->filter($query);
    		}
    		
    		if ($exceptArticleId) {
    			$query = $query->whereNotEqual('id', $exceptArticleId);
    		}
    
    		$query = $query->limit($limit);
    		
    		return $query;
		});
    }
    
    public static function search($query)
    {
        return self::getMany(function ($q) use ($query) {
			$queryParts = preg_split("/\s/", $query);
			
			foreach ($queryParts as $queryPart) {
				$decor = '%' . $queryPart . '%';
				$q = $q
					->whereRaw('(name_en like ? or name_ru like ?)', [ $decor, $decor ]);
			}

            return $q;
        })
        ->multiSort([
            'name_ru' => [ 'type' => Sort::STRING ],
            'category' => [ 'type' => Sort::NULL ],
        ]);
    }
    
    // props

    public function game()
    {
    	return Game::get($this->gameId);
    }

    public function category()
    {
        return $this->lazy(__FUNCTION__, function () {
            return ArticleCategory::get($this->cat);
        });
    }

    public function title()
    {
        $cat = $this->category();
        
        return $this->nameRu . ($cat ? " ({$cat->nameRu})" : '');
    }
    
    public function titleEn()
    {
        return $this->hideeng ? null : $this->nameEn;
    }
    
    public function titleFull()
    {
        $en = $this->titleEn();
        
        return $this->nameRu . ($en ? " ({$en})" : ''); 
    }

    public function url()
    {
        $cat = $this->category();
        
        return self::$linker->article($this->nameEn, $cat ? $cat->nameEn : null);
    }
    
    public function parsed()
    {
        return $this->parsedDescription();
    }
    
    public function subArticles()
    {
        return $this
            ->children()
            ->where(function ($item) {
                return $item->isPublished();
            })
            ->ascStr('name_ru');
    }
    
    public function breadcrumbs()
	{
	    $breadcrumbs = Collection::makeEmpty();
	    
		$article = $this->parent();
		
		while (!is_null($article)) {
			if (!$article->noBreadcrumb) {
				$breadcrumbs = $breadcrumbs->add($article);
			}

			$article = $article->parent();
		}
		
		return $breadcrumbs->reverse()->map(function ($a) {
		    return [
		        'url' => $a->url(),
				'text' => $a->nameRu,
				'title' => $a->titleEn()
            ];
		});
	}
    
    public function serialize()
    {
        $cat = $this->category();
        
        return [
            'id' => $this->id,
            'name_ru' => $this->nameRu,
            'name_en' => $this->nameEn,
            'category' => $cat ? $cat->serialize() : null,
        ];
    }
}
