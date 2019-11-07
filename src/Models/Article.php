<?php

namespace App\Models;

use App\Models\Interfaces\NewsSourceInterface;
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

class Article extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use CachedDescription, Children, FullPublish, Stamps, Tags;
    
    protected static $sortField = 'published_at';
    protected static $sortReverse = true;
    
    // traits
    
    protected static function getDescriptionField() : string
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
    
    public static function getAllByName(string $name, string $cat = null) : Query
    {
        $name = Strings::toSpaces($name);
        $cat = Strings::toSpaces($cat);

        $query = self::getBaseProtected();
        
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
    
    public static function getByName(string $name, string $cat = null) : ?self
    {
        return self::getAllByName($name, $cat)->one();
    }
    
    public static function getByAlias(string $name, string $cat = null) : ?self
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
    
    public static function getByNameOrAlias(string $name, string $cat = null) : ?self
    {
        return self::getByName($name, $cat) ?? self::getByAlias($name, $cat);
    }

    // props

    public function game() : ?Game
    {
        return Game::get($this->gameId);
    }

    public function largeImage() : ?string
    {
        $parsed = $this->parsed();
        
        return $parsed['large_image'] ?? null;
    }
    
    public function image() : ?string
    {
        $parsed = $this->parsed();
        
        return $parsed['image'] ?? null;
    }

    public function video() : ?string
    {
        $parsed = $this->parsed();
        
        return $parsed['video'] ?? null;
    }

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

    public function parsed() : array
    {
        return $this->parsedDescription();
    }
    
    public function parsedText() : string
    {
        return $this->parsed()['text'];
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

    // interfaces

    public static function search(string $searchQuery) : Collection
    {
        return self::getBasePublished()
            ->search($searchQuery, '(name_en like ? or name_ru like ?)', 2)
            ->all()
            ->multiSort(
                [
                    'name_ru' => ['type' => Sort::STRING],
                    'category' => ['type' => Sort::NULL],
                ]
            );
    }
    
    public function serialize() : ?array
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
        
        $code = self::$parser->joinTagParts($parts);
        
        return '[[' . $code . ']]';
    }
    
    // NewsSourceInterface

    public function url() : ?string
    {
        $cat = $this->category();
        
        return self::$linker->article(
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

    public static function getLatestNews(Game $game = null, int $exceptNewsId = null) : Query
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
    
    public function fullText() : string
    {
        return $this->lazy(
            function () {
                return self::$parser->parseCut(
                    $this->parsedText()
                );
            }
        );
    }
    
    public function shortText() : string
    {
        return $this->lazy(
            function () {
                return self::$parser->parseCut(
                    $this->parsedText(), $this->url(), false
                );
            }
        );
    }
}
