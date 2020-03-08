<?php

namespace App\Models;

use Plasticode\Collection;
use Plasticode\Models\Traits\CachedDescription;
use Plasticode\Query;
use Plasticode\Models\Traits\Children;
use Plasticode\Util\Sort;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

/**
 * @property integer $id
 * @property integer|null $parentId
 * @property integer|null $cat
 * @property string $nameRu
 * @property string $nameEn
 * @property integer $hideeng
 * @property string|null $origin
 * @property string|null $text
 * @property string|null $cache
 * @property integer $announce
 * @property integer $gameId
 * @property integer $noBreadcrumb
 * @property string|null $aliases
 * @property ContainerInterface $container
 */
class Article extends NewsSource
{
    use CachedDescription, Children;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;
    
    protected static function getDescriptionField() : string
    {
        return 'text';
    }
    
    public static function publishedOrphans() : Collection
    {
        return self::getPublished()
            ->whereNull('parent_id')
            ->all();
    }

    public static function getLatest(Game $game = null, int $limit = null, int $exceptId = null) : Query
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
    public static function lookup(string $name, int $cat = null, int $exceptId = null) : Query
    {
        $query = self::query()
            ->where('name_en', $name);
        
        if (strlen($cat) > 0) {
            $query = $query->where('cat', $cat);
        } else {
            $query = $query->whereNull('cat');
        }
            
        if ($exceptId > 0) {
            $query = $query->whereNotEqual('id', $exceptId);
        }
            
        return $query;
    }

    // props

    public function category() : ?ArticleCategory
    {
        return $this->lazy(
            function () {
                return ArticleCategory::get($this->cat);
            }
        );
    }

    public function title() : string
    {
        $cat = $this->category();
        
        return $this->nameRu . ($cat ? ' (' . $cat->nameRu . ')' : '');
    }
    
    public function titleEn() : ?string
    {
        return $this->hideeng ? null : $this->nameEn;
    }
    
    public function titleFull() : string
    {
        $en = $this->titleEn();
        
        return $this->nameRu . ($en ? ' (' . $en . ')' : ''); 
    }
    
    public function subArticles() : Collection
    {
        return $this
            ->children()
            ->where(
                function ($item) {
                    return $item->isPublished();
                }
            )
            ->ascStr('name_ru');
    }
    
    public function breadcrumbs() : Collection
    {
        $breadcrumbs = Collection::makeEmpty();
        
        $article = $this->parent();
        
        while (!is_null($article)) {
            if (!$article->noBreadcrumb) {
                $breadcrumbs = $breadcrumbs->add($article);
            }

            $article = $article->parent();
        }
        
        return $breadcrumbs
            ->reverse()
            ->map(
                function ($a) {
                    return [
                        'url' => $a->url(),
                        'text' => $a->nameRu,
                        'title' => $a->titleEn(),
                    ];
                }
            );
    }

    public static function search(string $searchQuery) : Collection
    {
        return self::getPublished()
            ->search($searchQuery, '(name_en like ? or name_ru like ?)', 2)
            ->all()
            ->multiSort(
                [
                    'name_ru' => ['type' => Sort::STRING],
                    'category' => ['type' => Sort::NULL],
                ]
            );
    }
    
    public function serialize() : array
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
        
        $cat = $this->category();
        
        if ($cat !== null) {
            $parts[] = $cat->nameEn;
        }
        
        if ($cat !== null || $this->nameRu !== $this->nameEn) {
            $parts[] = $this->nameRu;
        }
        
        return Strings::doubleBracketsTag(null, ...$parts);
    }
    
    // NewsSourceInterface

    public function url() : ?string
    {
        $cat = $this->category();
        
        return self::$container->linker->article(
            $this->nameEn,
            $cat ? $cat->nameEn : null
        );
    }
    
    private static function announced(Query $query) : Query
    {
        return $query->where('announce', 1);
    }
    
    public static function getNewsByTag(string $tag) : Query
    {
        $query = static::getByTag($tag);
        return self::announced($query);
    }

    public static function getLatestNews(?Game $game = null, int $exceptNewsId = null) : Query
    {
        $query = static::getLatest($game, null, $exceptNewsId);
        return self::announced($query);
    }
    
    public static function getNewsBefore(Game $game, string $date) : Query
    {
        return self::getLatestNews($game)
            ->whereLt('published_at', $date)
            ->orderByDesc('published_at');
    }
    
    public static function getNewsAfter(Game $game, string $date) : Query
    {
        return self::getLatestNews($game)
            ->whereGt('published_at', $date)
            ->orderByAsc('published_at');
    }
    
    public static function getNewsByYear(int $year) : Query
    {
        $query = self::getPublished()
            ->whereRaw('(year(published_at) = ?)', [$year]);
        
        return self::announced($query);
    }

    public function displayTitle() : string
    {
        return $this->nameRu;
    }
}
