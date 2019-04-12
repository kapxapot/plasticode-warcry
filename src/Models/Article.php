<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Models\DbModel;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Models\Traits\Children;
use Plasticode\Models\Traits\FullPublish;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tags;
use Plasticode\Util\Sort;
use Plasticode\Util\Strings;

use App\Models\Interfaces\NewsSourceInterface;

class Article extends DbModel implements SearchableInterface, NewsSourceInterface
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
    
    public static function publishedOrphans() : Collection
    {
        return self::getPublished()
            ->whereNull('parent_id')
            ->all();
    }

    // queries
    
    public static function getAllByName($name, $cat = null) : Query
    {
		$name = Strings::toSpaces($name);
		$cat = Strings::toSpaces($cat);

		$query = self::getProtected();
		
		if (is_numeric($name)) {
			return $query->where(self::$idField, $name);
		}
		
		$query = $query->where('name_en', $name);
	
		if ($cat) {
		    $category = ArticleCategory::getByName($cat);
		    
		    if ($category) {
    			return $query
    				->whereRaw('(cat = ? or cat is null)', [ $category->id ])
    				->orderByDesc('cat');
		    }
		}

		return $query->orderByAsc('cat');
	}

    public static function getLatest($game = null, $limit = null, $exceptId = null) : Query
    {
		$query = self::getPublished()
		    ->where('announce', 1);
	
		if ($game) {
			$query = $game->filter($query);
		}
		
		if ($exceptId) {
			$query = $query->whereNotEqual('id', $exceptId);
		}
		
		if ($limit) {
		    $query = $query->limit($limit);
		}
		
		return $query;
    }
    
    /**
     * Check article duplicates for validation.
     */
    public static function lookup($name, $cat, $exceptId) : Query
    {
	    $query = self::query()
	        ->where('name_en', $name);
    	
		if (strlen($cat) > 0) {
			$query = $query->where('cat', $cat);
		}
		else {
			$query = $query->whereNull('cat');
		}
    		
		if ($exceptId > 0) {
    		$query = $query->whereNotEqual('id', $exceptId);
    	}
    		
    	return $query;
    }
    
    public static function getByName($name, $cat = null)
    {
        return self::getAllByName($name, $cat)->one();
	}
	
	public static function getByAlias($name, $cat = null)
	{
		$name = Strings::toSpaces($name);
		$cat = Strings::toSpaces($cat);

	    $aliasParts[] = $name;
	    
	    if (strlen($cat) > 0) {
	        $aliasParts[] = $cat;
	    }
	    
        $alias = self::$parser->joinTagParts($aliasParts);

		return self::getProtected()
		    ->whereRaw('(aliases like ?)', [ '%' . $alias . '%' ])
		    ->one();
	}
	
	public static function getByNameOrAlias($name, $cat = null)
	{
	    return self::getByName($name, $cat) ?? self::getByAlias($name, $cat);
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

    public function parsed()
    {
        return $this->parsedDescription();
    }
    
    public function parsedText()
    {
        return $this->parsed()['text'];
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

    // interfaces

    public static function search($searchQuery) : Collection
    {
        return self::getBasePublished()
            ->search($searchQuery, '(name_en like ? or name_ru like ?)', 2)
            ->all()
            ->multiSort([
                'name_ru' => [ 'type' => Sort::STRING ],
                'category' => [ 'type' => Sort::NULL ],
            ]);
    }
    
    public function serialize()
    {
        $cat = $this->category();
        
        return [
            'id' => $this->getId(),
            'name_ru' => $this->nameRu,
            'name_en' => $this->nameEn,
            'category' => $cat ? $cat->serialize() : null,
            'tags' => Strings::toTags($this->tags),
        ];
    }
    
    public function code() : string
    {
        $parts[] = $this->nameEn;
        
        if ($this->category()) {
            $parts[] = $this->category()->nameEn;
        }
        
        $parts[] = $this->nameRu;
        
        $code = self::$parser->joinTagParts($parts);
        
        return "[[{$code}]]";
    }
    
    // NewsSourceInterface

    public function url()
    {
        $cat = $this->category();
        
        return self::$linker->article($this->nameEn, $cat ? $cat->nameEn : null);
    }
    
    private static function announced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
    
    public static function getNewsByTag($tag) : Query
    {
        $query = static::getByTag($tag);
        return self::announced($query);
    }

	public static function getLatestNews($game = null, $exceptNewsId = null) : Query
	{
	    $query = static::getLatest($game);
	    return self::announced($query);
	}
	
	public static function getNewsBefore($game, $date) : Query
	{
		return self::getLatestNews($game)
		    ->whereLt('published_at', $date)
		    ->orderByDesc('published_at');
	}
	
	public static function getNewsAfter($game, $date) : Query
	{
		return self::getLatestNews($game)
		    ->whereGt('published_at', $date)
		    ->orderByAsc('published_at');
	}
	
	public static function getNewsByYear($year) : Query
	{
		$query = self::getPublished()
		    ->whereRaw('(year(published_at) = ?)', [ $year ]);
		
		return self::announced($query);
	}

	public function displayTitle()
	{
	    return $this->nameRu;
	}
    
    public function fullText()
    {
        return $this->lazy(__FUNCTION__, function () {
            return self::$parser->parseCut($this->parsedText());
        });
    }
    
    public function shortText()
    {
        return $this->lazy(__FUNCTION__, function () {
            return self::$parser->parseCut($this->parsedText(), $this->url(), false);
        });
    }
}
